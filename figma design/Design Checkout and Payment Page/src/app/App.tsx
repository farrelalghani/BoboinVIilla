import { BookingSummary } from './components/BookingSummary';
import { PaymentMethod } from './components/PaymentMethod';

export default function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-2xl font-semibold mb-2">Checkout & Payment</h1>
          <p className="text-gray-600">Complete your villa booking securely</p>
        </div>

        {/* Two-column layout */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Left Column - Payment Method (2/3 width) */}
          <div className="lg:col-span-2">
            <PaymentMethod />
          </div>

          {/* Right Column - Booking Summary (1/3 width) */}
          <div className="lg:col-span-1">
            <div className="lg:sticky lg:top-8">
              <BookingSummary />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
