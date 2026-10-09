import { Menu, X } from 'lucide-react';
import { useState } from 'react';

export function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  return (
    <header className="bg-white shadow-sm sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center">
            <h1 className="text-[#3a6484] text-xl sm:text-2xl">boboinvilla</h1>
          </div>

          <nav className="hidden md:flex items-center gap-8">
            <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors">
              Browse Villas
            </a>
            <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors">
              Destinations
            </a>
            <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors">
              About
            </a>
            <button className="px-4 py-2 border border-[#3a6484] text-[#3a6484] rounded-lg hover:bg-[#3a6484] hover:text-white transition-colors">
              Sign In
            </button>
            <button className="px-4 py-2 bg-[#3a6484] text-white rounded-lg hover:bg-[#3a6484] transition-colors">
              Sign Up
            </button>
          </nav>

          <button
            className="md:hidden p-2"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
          >
            {isMenuOpen ? (
              <X className="w-6 h-6 text-[#3a6484]" />
            ) : (
              <Menu className="w-6 h-6 text-[#3a6484]" />
            )}
          </button>
        </div>

        {/* Mobile Menu */}
        {isMenuOpen && (
          <div className="md:hidden py-4 border-t border-[#3a6484]">
            <nav className="flex flex-col space-y-4">
              <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors py-2">
                Browse Villas
              </a>
              <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors py-2">
                Destinations
              </a>
              <a href="#" className="text-[#3a6484] hover:text-[#3a6484] transition-colors py-2">
                About
              </a>
              <button className="px-4 py-2 border border-[#3a6484] text-[#3a6484] rounded-lg hover:bg-[#3a6484] hover:text-white transition-colors text-left">
                Sign In
              </button>
              <button className="px-4 py-2 bg-[#3a6484] text-white rounded-lg hover:bg-[#3a6484] transition-colors">
                Sign Up
              </button>
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}
