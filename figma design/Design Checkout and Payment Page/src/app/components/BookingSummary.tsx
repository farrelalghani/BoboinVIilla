import { Calendar, Users } from 'lucide-react';
import { ImageWithFallback } from './figma/ImageWithFallback';

export function BookingSummary() {
  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6">
      <h2 className="text-lg font-semibold mb-4">Booking Summary</h2>
      
      {/* Villa Image */}
      <div className="mb-4 rounded-lg overflow-hidden">
        <ImageWithFallback
          src="https://images.unsplash.com/photo-1622015663084-307d19eabbbf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsdXh1cnklMjB2aWxsYSUyMGV4dGVyaW9yJTIwdHJvcGljYWx8ZW58MXx8fHwxNzc2MjcxMDIxfDA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
          alt="Sunset Paradise Villa"
          className="w-full h-48 object-cover"
        />
      </div>

      {/* Villa Name */}
      <h3 className="font-semibold text-base mb-3">Sunset Paradise Villa</h3>

      {/* Dates and Guests */}
      <div className="space-y-2 mb-4 pb-4 border-b border-gray-200">
        <div className="flex items-center text-sm text-gray-600">
          <Calendar className="w-4 h-4 mr-2" />
          <span>Apr 20, 2026 - Apr 25, 2026</span>
        </div>
        <div className="flex items-center text-sm text-gray-600">
          <Users className="w-4 h-4 mr-2" />
          <span>4 Guests</span>
        </div>
      </div>

      {/* Price Breakdown */}
      <div className="space-y-3">
        <div className="flex justify-between text-sm">
          <span className="text-gray-600">$450 × 5 nights</span>
          <span>$2,250</span>
        </div>
        <div className="flex justify-between text-sm">
          <span className="text-gray-600">Service fee</span>
          <span>$112</span>
        </div>
        <div className="border-t border-gray-200 pt-3 flex justify-between font-semibold">
          <span>Total</span>
          <span className="text-lg">$2,362</span>
        </div>
      </div>
    </div>
  );
}
