<?php

namespace App\Services;

use App\Models\Villa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $this->model  = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-flash-lite-latest');
    }

    /**
     * Menghasilkan jawaban AI berdasarkan riwayat percakapan dan konteks villa.
     *
     * @param array<int, array{role: string, text: string}> $history
     * @param string $userMessage
     * @return string
     */
    public function reply(array $history, string $userMessage): string
    {
        // Jika API Key belum disetel di .env, gunakan fallback responsif berbasis database lokal
        if (empty($this->apiKey)) {
            return $this->localFallbackReply($userMessage);
        }

        try {
            $systemInstruction = $this->buildSystemInstruction();
            $contents = $this->formatContents($history, $userMessage);

            $payload = [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 700,
                ],
            ];

            // Coba model utama (default ultra-fast: gemini-flash-lite-latest)
            $response = $this->callGeminiApi($this->model, $payload);

            if ($response && $response->successful()) {
                $candidates = $response->json('candidates');
                $text = $candidates[0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text)) {
                    return trim($text);
                }
            }

            // Fallback ke model alternatif jika model utama sibuk / unavailable
            $fallbackModel = ($this->model === 'gemini-flash-lite-latest') ? 'gemini-flash-latest' : 'gemini-flash-lite-latest';
            $fallbackResponse = $this->callGeminiApi($fallbackModel, $payload);
            if ($fallbackResponse && $fallbackResponse->successful()) {
                $candidates = $fallbackResponse->json('candidates');
                $text = $candidates[0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text)) {
                    return trim($text);
                }
            }

            Log::warning('Gemini API Non-200 Response: ' . ($response ? $response->body() : 'No response'));
            return $this->localFallbackReply($userMessage);
        } catch (\Throwable $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return $this->localFallbackReply($userMessage);
        }
    }

    /**
     * Mengirim request HTTP ke Google Generative Language API
     * Mendukung format API Key standar (AIza...) maupun Auth Key baru (AQ...)
     */
    protected function callGeminiApi(string $model, array $payload): ?\Illuminate\Http\Client\Response
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        return Http::timeout(7)
            ->withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type'   => 'application/json',
            ])
            ->post($endpoint, $payload);
    }

    /**
     * Membangun system instruction dengan konteks data villa terkini dari database.
     */
    protected function buildSystemInstruction(): string
    {
        $villasContext = $this->getVillasKnowledgeContext();

        return <<<PROMPT
Anda adalah "Boboin AI Concierge", asisten virtual resmi yang cerdas, ramah, dan solutif dari Boboin Villa (platform reservasi villa premium, website: https://boboinvilla.my.id).

TUGAS UTAMA:
1. Membantu calon tamu menemukan villa yang paling sesuai dengan kebutuhan mereka (jumlah tamu, lokasi, fasilitas seperti kolam renang/BBQ/kitchen, dan estimasi biaya).
2. Menjelaskan tata cara pemesanan, pengecekan status booking, serta metode pembayaran.
3. Memberikan rekomendasi destinasi wisata di sekitar villa yang relevan.

DATA VILLA RESMI YANG TERSEDIA DI DATABASE SAAT INI:
{$villasContext}

INFORMASI OPERASIONAL & ATURAN:
- Pemesanan dilakukan mandiri melalui website: pilih tanggal check-in & check-out -> tentukan jumlah tamu -> ajukan pesanan.
- Setelah memesan, tamu akan mendapatkan Kode Booking unik berformat BVL-XXXXXX.
- Tamu dapat mengecek status pesanan atau download invoice PDF kapan saja melalui menu "Cek Pesanan" (masukkan kode booking).
- Metode pembayaran: Transfer bank manual (tamu mengisi data diri, mentransfer ke rekening resmi Boboin Villa di halaman pembayaran, lalu mengunggah bukti transfer untuk diverifikasi admin dalam 1×24 jam).
- Bantuan darurat / WhatsApp Admin resmi: +62 857-2482-1667.

GAYA KOMUNIKASI:
- Bersikap ramah, sopan, antusias, dan profesional selayaknya concierge resort / hotel bintang 5.
- Jawab dalam bahasa yang sama dengan tamu (Bahasa Indonesia atau English).
- Gunakan format Markdown yang rapi (bold untuk nama villa & harga, bullet points untuk daftar fasilitas).
- Jangan mengarang data villa yang tidak ada di daftar database di atas. Jika data tidak ada, sampaikan dengan sopan dan tawarkan untuk menghubungi WhatsApp admin.
PROMPT;
    }

    /**
     * Mengambil ringkasan data seluruh villa yang berstatus aktif.
     */
    protected function getVillasKnowledgeContext(): string
    {
        return Cache::remember('ai_villas_knowledge_context', 300, function () {
            try {
                $villas = Villa::where('status', 'active')->with('peakSeasons')->get();

                if ($villas->isEmpty()) {
                    return "- Saat ini belum ada data villa aktif di database.";
                }

                $lines = [];
                foreach ($villas as $villa) {
                    $facilities = is_array($villa->facilities) ? implode(', ', $villa->facilities) : '-';
                    $destinations = '-';
                    if (is_array($villa->nearby_destinations)) {
                        $dList = array_map(function ($d) {
                            return is_array($d) ? ($d['name'] ?? '') . ' (' . ($d['distance'] ?? '') . ')' : '';
                        }, $villa->nearby_destinations);
                        $destinations = implode(', ', array_filter($dList));
                    }

                    $weekdayFmt = 'Rp ' . number_format($villa->weekday_price, 0, ',', '.');
                    $weekendFmt = 'Rp ' . number_format($villa->weekend_price, 0, ',', '.');

                    $lines[] = "• **{$villa->name}** (Kota: {$villa->city}, Alamat: {$villa->address}):\n" .
                               "  - Kapasitas: Hingga {$villa->capacity} orang.\n" .
                               "  - Harga: Weekday {$weekdayFmt}/malam | Weekend {$weekendFmt}/malam.\n" .
                               "  - Fasilitas: {$facilities}.\n" .
                               "  - Destinasi Terdekat: {$destinations}.";
                }

                return implode("\n\n", $lines);
            } catch (\Throwable $e) {
                return "- Gagal memuat data villa: " . $e->getMessage();
            }
        });
    }

    /**
     * Memformat riwayat percakapan agar sesuai dengan skema contents Gemini API.
     *
     * @param array<int, array{role: string, text: string}> $history
     * @param string $userMessage
     * @return array<int, array{role: string, parts: array<int, array{text: string}>}>
     */
    protected function formatContents(array $history, string $userMessage): array
    {
        $contents = [];

        // Ambil maksimal 8 pesan terakhir agar context window tetap efisien
        $recentHistory = array_slice($history, -8);

        foreach ($recentHistory as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role'  => $role,
                'parts' => [
                    ['text' => $msg['text']],
                ],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [
                ['text' => $userMessage],
            ],
        ];

        return $contents;
    }

    /**
     * Fallback cerdas jika API key belum diisi atau terjadi kendala jaringan sementara.
     */
    protected function localFallbackReply(string $userMessage): string
    {
        $lower = strtolower($userMessage);

        try {
            $villas = Villa::where('status', 'active')->get();
            $villaCount = $villas->count();
            $firstVilla = $villas->first();

            if (str_contains($lower, 'rekomendasi') || str_contains($lower, 'villa') || str_contains($lower, 'kolam') || str_contains($lower, 'kapasitas')) {
                if ($firstVilla) {
                    $priceFmt = 'Rp ' . number_format($firstVilla->weekday_price, 0, ',', '.');
                    $fac = is_array($firstVilla->facilities) ? implode(', ', array_slice($firstVilla->facilities, 0, 4)) : 'Fasilitas Lengkap';
                    return "Halo! Kami memiliki **{$villaCount} pilihan villa premium** yang siap menyambut Anda. Salah satu rekomendasi favorit kami adalah **{$firstVilla->name}** di {$firstVilla->city} (kapasitas hingga {$firstVilla->capacity} orang, fasilitas: {$fac}) mulai dari **{$priceFmt}/malam**. Anda bisa melihat daftar lengkapnya di menu **Cari Villa**!";
                }
            }

            if (str_contains($lower, 'pesan') || str_contains($lower, 'cara') || str_contains($lower, 'booking')) {
                return "Cara pemesanan di Boboin Villa sangat mudah:\n1. Buka menu **Cari Villa** dan pilih villa yang Anda sukai.\n2. Tentukan tanggal check-in, check-out, dan jumlah tamu.\n3. Klik **Ajukan Pemesanan** untuk memperoleh Kode Booking unik (BVL-XXXXXX).\n4. Lakukan pembayaran dan cek status reservasi Anda di menu **Cek Pesanan**.";
            }

            if (str_contains($lower, 'bayar') || str_contains($lower, 'transfer') || str_contains($lower, 'rekening')) {
                return "Boboin Villa menggunakan metode pembayaran **Transfer Bank Manual**. Setelah pesanan disetujui admin, informasi rekening resmi dan formulir unggah bukti transfer akan tersedia pada halaman pembayaran di menu **Cek Pesanan**.";
            }

            if (str_contains($lower, 'bantuan') || str_contains($lower, 'admin') || str_contains($lower, 'wa') || str_contains($lower, 'whatsapp')) {
                return "Butuh bantuan cepat atau konfirmasi khusus? Tim Customer Service Boboin Villa siap membantu Anda via WhatsApp resmi di **+62 857-2482-1667**.";
            }

            return "Halo! Selamat datang di **Boboin Villa AI Concierge**. Saya dapat membantu Anda seputar rekomendasi villa, fasilitas, harga, atau tata cara pemesanan. Ada yang bisa saya bantu untuk rencana liburan Anda?";
        } catch (\Throwable $e) {
            return "Halo! Selamat datang di Boboin Villa. Silakan jelajahi pilihan villa terbaik kami di menu Cari Villa atau hubungi WhatsApp kami untuk bantuan pemesanan.";
        }
    }
}
