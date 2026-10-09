import { VillaCard } from './VillaCard';

const villas = [
  {
    id: 1,
    image: 'https://images.unsplash.com/photo-1673964566152-2aee6bc89929?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Ocean View Paradise',
    location: 'Bali, Indonesia',
    rating: 4.9,
    reviews: 127,
    price: 299
  },
  {
    id: 2,
    image: 'https://images.unsplash.com/photo-1758717152007-6a2eb7299409?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwyfHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Overwater Bungalow',
    location: 'Maldives',
    rating: 5.0,
    reviews: 203,
    price: 599
  },
  {
    id: 3,
    image: 'https://images.unsplash.com/photo-1772398539093-fc7b4a6b1bfc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwzfHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Hillside Luxury Estate',
    location: 'Phuket, Thailand',
    rating: 4.8,
    reviews: 156,
    price: 450
  },
  {
    id: 4,
    image: 'https://images.unsplash.com/photo-1605538032432-a9f0c8d9baac?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw0fHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Palm Tree Retreat',
    location: 'Philippines',
    rating: 4.7,
    reviews: 89,
    price: 225
  },
  {
    id: 5,
    image: 'https://images.unsplash.com/photo-1770185998570-db739db7af47?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw1fHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Beachfront Resort Villa',
    location: 'Cancun, Mexico',
    rating: 4.9,
    reviews: 178,
    price: 399
  },
  {
    id: 6,
    image: 'https://images.unsplash.com/photo-1759372945658-1e9f56e751bd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw2fHx0cm9waWNhbCUyMHZpbGxhJTIwYmVhY2glMjBsdXh1cnl8ZW58MXx8fHwxNzc1NTA4NTMzfDA&ixlib=rb-4.1.0&q=80&w=1080',
    name: 'Twilight Pool Villa',
    location: 'Bali, Indonesia',
    rating: 4.8,
    reviews: 142,
    price: 350
  }
];

export function FeaturedVillas() {
  return (
    <section className="py-12 sm:py-16 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8 sm:mb-12">
          <h2 className="mb-3 sm:mb-4 text-2xl sm:text-3xl">Featured Villas</h2>
          <p className="text-[#3a6484] text-base sm:text-lg max-w-2xl mx-auto px-4">
            Handpicked luxury villas in the world's most beautiful destinations
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          {villas.map((villa) => (
            <VillaCard key={villa.id} {...villa} />
          ))}
        </div>

        <div className="text-center mt-8 sm:mt-12">
          <button className="px-6 sm:px-8 py-2.5 sm:py-3 border-2 border-[#3a6484] text-[#3a6484] rounded-lg hover:bg-[#3a6484] hover:text-white transition-colors text-sm sm:text-base">
            View All Villas
          </button>
        </div>
      </div>
    </section>
  );
}
