import { Star, MapPin } from 'lucide-react';
import { ImageWithFallback } from './figma/ImageWithFallback';

interface VillaCardProps {
  image: string;
  name: string;
  location: string;
  rating: number;
  reviews: number;
  price: number;
}

export function VillaCard({ image, name, location, rating, reviews, price }: VillaCardProps) {
  return (
    <div className="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-shadow cursor-pointer group">
      <div className="relative h-48 sm:h-56 md:h-64 overflow-hidden">
        <ImageWithFallback
          src={image}
          alt={name}
          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
        />
        <div className="absolute top-3 right-3 sm:top-4 sm:right-4 bg-white px-2.5 py-1 sm:px-3 sm:py-1 rounded-full shadow-md">
          <span className="text-xs sm:text-sm">Featured</span>
        </div>
      </div>

      <div className="p-4 sm:p-5">
        <div className="flex items-start justify-between mb-2">
          <h3 className="flex-1 text-base sm:text-lg">{name}</h3>
          <div className="flex items-center gap-1 ml-2 flex-shrink-0">
            <Star className="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-[#3a6484] text-[#3a6484]" />
            <span className="text-xs sm:text-sm">{rating}</span>
          </div>
        </div>

        <div className="flex items-center gap-1 text-[#3a6484] mb-3 sm:mb-4">
          <MapPin className="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" />
          <span className="text-xs sm:text-sm">{location}</span>
        </div>

        <div className="flex items-center justify-between pt-3 sm:pt-4 border-t border-[#3a6484]/20">
          <div>
            <span className="text-[#3a6484] text-base sm:text-lg">${price}</span>
            <span className="text-[#3a6484] text-xs sm:text-sm"> / night</span>
          </div>
          <span className="text-xs sm:text-sm text-[#3a6484]">{reviews} reviews</span>
        </div>
      </div>
    </div>
  );
}
