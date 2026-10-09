import { LayoutDashboard, Home, Calendar, DollarSign, BookOpen } from 'lucide-react';

interface SidebarProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

export function Sidebar({ activeTab, onTabChange }: SidebarProps) {
  const menuItems = [
    { id: 'dashboard', label: 'Dashboard Overview', icon: LayoutDashboard },
    { id: 'properties', label: 'My Properties', icon: Home },
    { id: 'bookings', label: 'Bookings', icon: BookOpen },
    { id: 'calendar', label: 'Availability Calendar', icon: Calendar },
    { id: 'payouts', label: 'Payouts', icon: DollarSign },
  ];

  return (
    <aside className="w-64 bg-[#1e3a8a] text-white min-h-screen flex flex-col">
      <div className="p-6 border-b border-blue-700">
        <h1 className="text-2xl font-bold">Villa Host</h1>
        <p className="text-blue-200 text-sm mt-1">Property Management</p>
      </div>

      <nav className="flex-1 p-4">
        <ul className="space-y-2">
          {menuItems.map((item) => {
            const Icon = item.icon;
            return (
              <li key={item.id}>
                <button
                  onClick={() => onTabChange(item.id)}
                  className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                    activeTab === item.id
                      ? 'bg-blue-600 text-white'
                      : 'text-blue-100 hover:bg-blue-700'
                  }`}
                >
                  <Icon className="w-5 h-5" />
                  <span>{item.label}</span>
                </button>
              </li>
            );
          })}
        </ul>
      </nav>

      <div className="p-4 border-t border-blue-700">
        <div className="flex items-center gap-3 px-4 py-3">
          <div className="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center">
            <span className="text-sm font-semibold">JD</span>
          </div>
          <div>
            <p className="font-medium text-sm">John Doe</p>
            <p className="text-blue-200 text-xs">Host Account</p>
          </div>
        </div>
      </div>
    </aside>
  );
}
