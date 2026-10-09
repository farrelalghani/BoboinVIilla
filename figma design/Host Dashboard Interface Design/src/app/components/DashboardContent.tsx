import { DollarSign, Calendar, Bell } from 'lucide-react';
import { MetricCard } from './MetricCard';
import { AddPropertyForm } from './AddPropertyForm';

export function DashboardContent() {
  return (
    <div className="space-y-6">
      {/* Metrics Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <MetricCard
          title="Total Revenue"
          value="$24,580"
          icon={DollarSign}
          trend="+12.5% from last month"
          trendUp={true}
        />
        <MetricCard
          title="Active Bookings"
          value="18"
          icon={Calendar}
          trend="+3 new this week"
          trendUp={true}
        />
        <MetricCard
          title="New Requests"
          value="7"
          icon={Bell}
          trend="Awaiting response"
          trendUp={false}
        />
      </div>

      {/* Add Property Form */}
      <AddPropertyForm />

      {/* Recent Activity */}
      <div className="bg-white rounded-lg shadow-md border border-gray-200 p-6">
        <h3 className="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h3>
        <div className="space-y-4">
          {[
            { type: 'booking', title: 'New booking for Ocean View Villa', time: '2 hours ago', status: 'confirmed' },
            { type: 'request', title: 'Booking request for Mountain Retreat', time: '5 hours ago', status: 'pending' },
            { type: 'payment', title: 'Payment received - $2,450', time: '1 day ago', status: 'completed' },
            { type: 'review', title: 'New 5-star review on Sunset Paradise', time: '2 days ago', status: 'positive' },
          ].map((activity, index) => (
            <div key={index} className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
              <div className={`w-2 h-2 rounded-full mt-2 ${
                activity.status === 'confirmed' || activity.status === 'completed' || activity.status === 'positive'
                  ? 'bg-green-500'
                  : activity.status === 'pending'
                  ? 'bg-yellow-500'
                  : 'bg-blue-500'
              }`} />
              <div className="flex-1">
                <p className="text-gray-900 font-medium">{activity.title}</p>
                <p className="text-gray-500 text-sm mt-1">{activity.time}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
