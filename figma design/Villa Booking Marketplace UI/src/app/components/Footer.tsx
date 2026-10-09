import { Facebook, Instagram, Twitter, Youtube } from 'lucide-react';

export function Footer() {
  return (
    <footer className="bg-[#3a6484] text-white pt-12 sm:pt-16 pb-6 sm:pb-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 mb-8 sm:mb-12">
          <div className="sm:col-span-2 md:col-span-1">
            <h3 className="text-white mb-4 sm:mb-6 text-lg sm:text-xl">VillaStay</h3>
            <p className="text-white/80 mb-4 text-sm sm:text-base">
              Your gateway to luxury villa rentals in the world's most beautiful destinations.
            </p>
            <div className="flex gap-3 sm:gap-4">
              <a href="#" className="text-white/80 hover:text-white transition-colors">
                <Facebook className="w-5 h-5" />
              </a>
              <a href="#" className="text-white/80 hover:text-white transition-colors">
                <Instagram className="w-5 h-5" />
              </a>
              <a href="#" className="text-white/80 hover:text-white transition-colors">
                <Twitter className="w-5 h-5" />
              </a>
              <a href="#" className="text-white/80 hover:text-white transition-colors">
                <Youtube className="w-5 h-5" />
              </a>
            </div>
          </div>

          <div>
            <h4 className="text-white mb-3 sm:mb-4 text-sm sm:text-base">Company</h4>
            <ul className="space-y-2 text-sm sm:text-base">
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">About Us</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Careers</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Press</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Blog</a></li>
            </ul>
          </div>

          <div>
            <h4 className="text-white mb-3 sm:mb-4 text-sm sm:text-base">Support</h4>
            <ul className="space-y-2 text-sm sm:text-base">
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Help Center</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Safety Information</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Cancellation Options</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Contact Us</a></li>
            </ul>
          </div>

          <div>
            <h4 className="text-white mb-3 sm:mb-4 text-sm sm:text-base">Hosting</h4>
            <ul className="space-y-2 text-sm sm:text-base">
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">List Your Property</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Host Resources</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Community Forum</a></li>
              <li><a href="#" className="text-white/80 hover:text-white transition-colors">Responsible Hosting</a></li>
            </ul>
          </div>
        </div>

        <div className="border-t border-white/20 pt-6 sm:pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-white/80 text-xs sm:text-sm text-center md:text-left">
            © 2026 VillaStay. All rights reserved.
          </p>
          <div className="flex flex-wrap justify-center gap-4 sm:gap-6 text-xs sm:text-sm">
            <a href="#" className="text-white/80 hover:text-white transition-colors">Privacy Policy</a>
            <a href="#" className="text-white/80 hover:text-white transition-colors">Terms of Service</a>
            <a href="#" className="text-white/80 hover:text-white transition-colors">Sitemap</a>
          </div>
        </div>
      </div>
    </footer>
  );
}
