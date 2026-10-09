import { Search, MapPin, Calendar, Users } from 'lucide-react';

export function Hero() {
  return (
    <div className="relative min-h-[500px] sm:h-[600px] bg-gradient-to-br from-[#3a6484] to-[#3a6484] overflow-hidden">
      <div className="absolute inset-0 opacity-20">
        <div className="absolute top-10 left-10 w-64 h-64 bg-white rounded-full blur-3xl"></div>
        <div className="absolute bottom-10 right-10 w-96 h-96 bg-[#3a6484] rounded-full blur-3xl"></div>
      </div>

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center items-center text-center py-12 sm:py-0">
        <h1 className="text-white mb-4 text-3xl sm:text-4xl lg:text-5xl">
          Find Your Perfect Villa Escape
        </h1>
        <p className="text-white/90 text-base sm:text-lg lg:text-xl mb-8 sm:mb-12 max-w-2xl px-4">
          Discover luxury villas in tropical paradise destinations around the world
        </p>

        <div className="w-full max-w-5xl bg-white rounded-2xl shadow-2xl p-4 sm:p-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
            <div className="flex items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-[#3a6484] rounded-lg">
              <MapPin className="w-4 h-4 sm:w-5 sm:h-5 text-white flex-shrink-0" />
              <div className="flex-1 text-left min-w-0">
                <label className="text-xs sm:text-sm text-white block">Location</label>
                <input
                  type="text"
                  placeholder="Where to?"
                  className="w-full bg-transparent border-none outline-none text-white placeholder-white/70 text-sm"
                />
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-[#3a6484] rounded-lg">
              <Calendar className="w-4 h-4 sm:w-5 sm:h-5 text-white flex-shrink-0" />
              <div className="flex-1 text-left min-w-0">
                <label className="text-xs sm:text-sm text-white block">Check-in</label>
                <input
                  type="text"
                  placeholder="Add dates"
                  className="w-full bg-transparent border-none outline-none text-white placeholder-white/70 text-sm"
                />
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-[#3a6484] rounded-lg">
              <Calendar className="w-4 h-4 sm:w-5 sm:h-5 text-white flex-shrink-0" />
              <div className="flex-1 text-left min-w-0">
                <label className="text-xs sm:text-sm text-white block">Check-out</label>
                <input
                  type="text"
                  placeholder="Add dates"
                  className="w-full bg-transparent border-none outline-none text-white placeholder-white/70 text-sm"
                />
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3 p-3 sm:p-4 bg-[#3a6484] rounded-lg">
              <Users className="w-4 h-4 sm:w-5 sm:h-5 text-white flex-shrink-0" />
              <div className="flex-1 text-left min-w-0">
                <label className="text-xs sm:text-sm text-white block">Guests</label>
                <input
                  type="text"
                  placeholder="Add guests"
                  className="w-full bg-transparent border-none outline-none text-white placeholder-white/70 text-sm"
                />
              </div>
            </div>
          </div>

          <button className="mt-4 sm:mt-6 w-full md:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-[#3a6484] text-white rounded-lg hover:bg-[#3a6484] transition-colors flex items-center justify-center gap-2 mx-auto text-sm sm:text-base">
            <Search className="w-4 h-4 sm:w-5 sm:h-5" />
            Search Villas
          </button>
        </div>
      </div>
    </div>
  );
}
