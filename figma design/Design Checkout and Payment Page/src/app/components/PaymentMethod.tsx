import { VirtualAccountDetails } from './VirtualAccountDetails';

export function PaymentMethod() {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6">
      <h2 className="text-lg font-semibold mb-4">Payment Method</h2>
      
      {/* Payment Method Options */}
      <div className="mb-6">
        <div className="flex items-center gap-3 p-4 border-2 border-blue-500 bg-blue-50 rounded-lg">
          <div className="w-5 h-5 rounded-full border-2 border-blue-500 flex items-center justify-center">
            <div className="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
          </div>
          <div className="flex-1">
            <div className="font-medium">Virtual Account (Bank Transfer)</div>
            <div className="text-xs text-gray-500">Pay via bank transfer to virtual account</div>
          </div>
        </div>
      </div>

      {/* Virtual Account Details */}
      <VirtualAccountDetails />
    </div>
  );
}
