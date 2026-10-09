import { useState, useEffect } from 'react';
import { Copy, CheckCircle2, ChevronDown } from 'lucide-react';
import * as Accordion from '@radix-ui/react-accordion';

export function VirtualAccountDetails() {
  const [copied, setCopied] = useState(false);
  const [timeLeft, setTimeLeft] = useState(86399); // 23:59:59 in seconds

  const virtualAccountNumber = "8808 1234 5678 9012";

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft((prev) => (prev > 0 ? prev - 1 : 0));
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  const formatTime = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  };

  const handleCopy = () => {
    navigator.clipboard.writeText(virtualAccountNumber.replace(/\s/g, ''));
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="space-y-6">
      {/* Selected Bank */}
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-blue-600 rounded flex items-center justify-center text-white font-semibold">
              BCA
            </div>
            <div>
              <div className="font-medium">Bank Central Asia</div>
              <div className="text-xs text-gray-500">Virtual Account</div>
            </div>
          </div>
          <div className="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
            <CheckCircle2 className="w-3 h-3 text-white" />
          </div>
        </div>
      </div>

      {/* Virtual Account Number */}
      <div className="bg-blue-50 border-2 border-blue-200 rounded-lg p-5">
        <div className="text-sm text-gray-600 mb-2">Virtual Account Number</div>
        <div className="flex items-center justify-between">
          <div className="font-mono text-2xl font-semibold tracking-wide">
            {virtualAccountNumber}
          </div>
          <button
            onClick={handleCopy}
            className="ml-4 p-2 hover:bg-blue-100 rounded-md transition-colors"
            aria-label="Copy virtual account number"
          >
            {copied ? (
              <CheckCircle2 className="w-5 h-5 text-green-600" />
            ) : (
              <Copy className="w-5 h-5 text-gray-600" />
            )}
          </button>
        </div>
      </div>

      {/* Expiration Timer */}
      <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div className="flex items-center justify-between">
          <span className="text-sm text-gray-700">Complete payment within</span>
          <span className="font-mono text-lg font-semibold text-amber-700">
            {formatTime(timeLeft)}
          </span>
        </div>
      </div>

      {/* Amount to Pay */}
      <div className="bg-white rounded-lg border border-gray-200 p-4">
        <div className="flex items-center justify-between">
          <span className="text-sm text-gray-600">Total Amount</span>
          <span className="text-2xl font-semibold">$2,362</span>
        </div>
      </div>

      {/* Payment Instructions */}
      <div className="bg-white rounded-lg border border-gray-200">
        <div className="p-4 border-b border-gray-200">
          <h3 className="font-semibold">Payment Instructions</h3>
        </div>
        
        <Accordion.Root type="single" collapsible className="w-full">
          <Accordion.Item value="mobile-banking">
            <Accordion.Header>
              <Accordion.Trigger className="flex items-center justify-between w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors group">
                <span className="font-medium">Mobile Banking</span>
                <ChevronDown className="w-5 h-5 text-gray-400 transition-transform duration-200 group-data-[state=open]:rotate-180" />
              </Accordion.Trigger>
            </Accordion.Header>
            <Accordion.Content className="px-4 pb-4 pt-1 text-sm text-gray-600 data-[state=open]:animate-accordion-down data-[state=closed]:animate-accordion-up overflow-hidden">
              <ol className="list-decimal list-inside space-y-2">
                <li>Open your BCA Mobile Banking app</li>
                <li>Select <strong>m-Transfer</strong> menu</li>
                <li>Choose <strong>BCA Virtual Account</strong></li>
                <li>Enter the Virtual Account number: <span className="font-mono font-semibold">{virtualAccountNumber.replace(/\s/g, '')}</span></li>
                <li>Verify the payment details and amount</li>
                <li>Enter your PIN to confirm</li>
                <li>Save the transaction receipt</li>
              </ol>
            </Accordion.Content>
          </Accordion.Item>

          <Accordion.Item value="atm" className="border-t border-gray-200">
            <Accordion.Header>
              <Accordion.Trigger className="flex items-center justify-between w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors group">
                <span className="font-medium">ATM</span>
                <ChevronDown className="w-5 h-5 text-gray-400 transition-transform duration-200 group-data-[state=open]:rotate-180" />
              </Accordion.Trigger>
            </Accordion.Header>
            <Accordion.Content className="px-4 pb-4 pt-1 text-sm text-gray-600 data-[state=open]:animate-accordion-down data-[state=closed]:animate-accordion-up overflow-hidden">
              <ol className="list-decimal list-inside space-y-2">
                <li>Insert your ATM card and enter your PIN</li>
                <li>Select <strong>Other Transactions</strong></li>
                <li>Select <strong>Transfer</strong></li>
                <li>Select <strong>To BCA Virtual Account</strong></li>
                <li>Enter the Virtual Account number: <span className="font-mono font-semibold">{virtualAccountNumber.replace(/\s/g, '')}</span></li>
                <li>Verify the payment details and total amount</li>
                <li>Confirm and complete the transaction</li>
                <li>Keep your receipt as proof of payment</li>
              </ol>
            </Accordion.Content>
          </Accordion.Item>
        </Accordion.Root>
      </div>

      {/* Security Notice */}
      <div className="bg-gray-50 rounded-lg p-4 text-xs text-gray-600">
        <p>
          🔒 <strong>Secure Payment:</strong> Your payment is protected with bank-level encryption. 
          Never share your PIN or OTP with anyone. We will never ask for this information.
        </p>
      </div>
    </div>
  );
}
