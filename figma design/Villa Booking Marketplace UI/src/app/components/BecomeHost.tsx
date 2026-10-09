import { Home, DollarSign, Users, TrendingUp } from 'lucide-react';

export function BecomeHost() {
  return (
    <section className="py-12 sm:py-16 lg:py-20 bg-gradient-to-br from-[#3a6484] to-[#3a6484] relative overflow-hidden">
      <div className="absolute inset-0 opacity-10">
        <div className="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div className="absolute bottom-0 right-0 w-96 h-96 bg-[#3a6484] rounded-full blur-3xl"></div>
      </div>

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-10 lg:gap-12 items-center">
          <div className="text-white">
            <h2 className="text-white mb-4 sm:mb-6 text-2xl sm:text-3xl lg:text-4xl">
              Become a Host & List Your Villa
            </h2>
            <p className="text-white/90 text-sm sm:text-base lg:text-lg mb-6 sm:mb-8">
              Join thousands of property owners earning extra income by sharing their beautiful villas with travelers from around the world.
            </p>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
              <div className="flex items-start gap-2.5 sm:gap-3">
                <div className="bg-white/20 p-2.5 sm:p-3 rounded-lg flex-shrink-0">
                  <DollarSign className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                <div>
                  <h4 className="text-white mb-1 text-sm sm:text-base">Earn More</h4>
                  <p className="text-white/80 text-xs sm:text-sm">Set your own prices and maximize your rental income</p>
                </div>
              </div>

              <div className="flex items-start gap-2.5 sm:gap-3">
                <div className="bg-white/20 p-2.5 sm:p-3 rounded-lg flex-shrink-0">
                  <Users className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                <div>
                  <h4 className="text-white mb-1 text-sm sm:text-base">Global Reach</h4>
                  <p className="text-white/80 text-xs sm:text-sm">Connect with travelers worldwide</p>
                </div>
              </div>

              <div className="flex items-start gap-2.5 sm:gap-3">
                <div className="bg-white/20 p-2.5 sm:p-3 rounded-lg flex-shrink-0">
                  <Home className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                <div>
                  <h4 className="text-white mb-1 text-sm sm:text-base">Full Control</h4>
                  <p className="text-white/80 text-xs sm:text-sm">You decide when to rent and who to host</p>
                </div>
              </div>

              <div className="flex items-start gap-2.5 sm:gap-3">
                <div className="bg-white/20 p-2.5 sm:p-3 rounded-lg flex-shrink-0">
                  <TrendingUp className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                <div>
                  <h4 className="text-white mb-1 text-sm sm:text-base">Easy Setup</h4>
                  <p className="text-white/80 text-xs sm:text-sm">List your property in minutes</p>
                </div>
              </div>
            </div>

            <button className="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-white text-[#3a6484] rounded-lg hover:bg-white/90 transition-colors text-sm sm:text-base">
              List Your Property
            </button>
          </div>

          <div className="mt-8 lg:mt-0">
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-4 sm:p-6 lg:p-8 border border-white/20">
              <div className="space-y-4 sm:space-y-6">
                <div className="bg-white rounded-xl p-4 sm:p-6 shadow-lg">
                  <div className="flex items-center justify-between mb-3 sm:mb-4">
                    <span className="text-[#3a6484] text-xs sm:text-sm">Average Monthly Earnings</span>
                    <TrendingUp className="w-4 h-4 sm:w-5 sm:h-5 text-[#3a6484]" />
                  </div>
                  <div className="text-3xl sm:text-4xl text-[#3a6484] mb-1 sm:mb-2">$8,500</div>
                  <div className="text-xs sm:text-sm text-[#3a6484]">Based on similar properties</div>
                </div>

                <div className="bg-white rounded-xl p-4 sm:p-6 shadow-lg">
                  <div className="text-xs sm:text-sm text-[#3a6484] mb-1 sm:mb-2">Active Hosts</div>
                  <div className="text-2xl sm:text-3xl text-[#3a6484] mb-1">12,000+</div>
                  <div className="text-xs sm:text-sm text-[#3a6484]">Trusted property owners</div>
                </div>

                <div className="bg-white rounded-xl p-4 sm:p-6 shadow-lg">
                  <div className="text-xs sm:text-sm text-[#3a6484] mb-1 sm:mb-2">Average Response Time</div>
                  <div className="text-2xl sm:text-3xl text-[#3a6484] mb-1">2 hours</div>
                  <div className="text-xs sm:text-sm text-[#3a6484]">Our team is here to help</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
