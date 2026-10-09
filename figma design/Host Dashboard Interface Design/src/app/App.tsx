import { useState } from 'react';
import { Sidebar } from './components/Sidebar';
import { DashboardContent } from './components/DashboardContent';

export default function App() {
  const [activeTab, setActiveTab] = useState('dashboard');

  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar activeTab={activeTab} onTabChange={setActiveTab} />

      <main className="flex-1 overflow-auto">
        <div className="max-w-7xl mx-auto p-8">
          <div className="mb-8">
            <h2 className="text-3xl font-bold text-gray-900">
              {activeTab === 'dashboard' && 'Dashboard Overview'}
              {activeTab === 'properties' && 'My Properties'}
              {activeTab === 'bookings' && 'Bookings'}
              {activeTab === 'calendar' && 'Availability Calendar'}
              {activeTab === 'payouts' && 'Payouts'}
            </h2>
            <p className="text-gray-600 mt-1">
              {activeTab === 'dashboard' && 'Manage your properties and track performance'}
              {activeTab === 'properties' && 'View and manage all your listed properties'}
              {activeTab === 'bookings' && 'Track all your bookings and reservations'}
              {activeTab === 'calendar' && 'Manage availability for all properties'}
              {activeTab === 'payouts' && 'View earnings and payment history'}
            </p>
          </div>

          {activeTab === 'dashboard' && <DashboardContent />}

          {activeTab !== 'dashboard' && (
            <div className="bg-white rounded-lg shadow-md border border-gray-200 p-12 text-center">
              <p className="text-gray-500 text-lg">
                This section is under development. Please check back soon!
              </p>
            </div>
          )}
        </div>
      </main>
    </div>
  );
}