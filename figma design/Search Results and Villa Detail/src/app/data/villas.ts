export interface Villa {
  id: number;
  name: string;
  location: string;
  description: string;
  shortDescription: string;
  price: number;
  rating: number;
  reviews: number;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  image: string;
  gallery: string[];
  amenities: string[];
  host: {
    name: string;
    avatar: string;
    joined: string;
    description: string;
  };
  guestReviews: {
    id: number;
    author: string;
    avatar: string;
    rating: number;
    date: string;
    comment: string;
  }[];
}

export const villas: Villa[] = [
  {
    id: 1,
    name: "Ocean View Paradise Villa",
    location: "Bali, Indonesia",
    description: "Experience ultimate luxury in this stunning oceanfront villa with panoramic views, infinity pool, and direct beach access. The property features modern architecture with traditional Balinese touches, spacious living areas, and a private chef available upon request. Perfect for families or groups seeking a tranquil escape with all the amenities of a five-star resort.",
    shortDescription: "Stunning oceanfront villa with infinity pool and beach access",
    price: 450,
    rating: 4.9,
    reviews: 127,
    bedrooms: 5,
    bathrooms: 4,
    guests: 10,
    image: "https://images.unsplash.com/photo-1758692513983-ebf181d2ec2a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1758692513983-ebf181d2ec2a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
      "https://images.unsplash.com/photo-1757439402115-c3c496fe81ec?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
      "https://images.unsplash.com/photo-1711110065918-388182f86e00?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
      "https://images.unsplash.com/photo-1760943013869-65a30a4fafd1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Beach Access", "Parking", "Garden", "BBQ"],
    host: {
      name: "Sarah Johnson",
      avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop",
      joined: "2020",
      description: "Superhost with 5 years of experience. I love sharing my beautiful properties with guests from around the world.",
    },
    guestReviews: [
      {
        id: 1,
        author: "Michael Chen",
        avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop",
        rating: 5,
        date: "March 2026",
        comment: "Absolutely stunning property! The views were breathtaking and the villa exceeded all expectations. Sarah was an excellent host.",
      },
      {
        id: 2,
        author: "Emma Wilson",
        avatar: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop",
        rating: 5,
        date: "February 2026",
        comment: "Perfect vacation spot! The infinity pool was amazing and the staff were incredibly helpful. Highly recommend!",
      },
      {
        id: 3,
        author: "James Rodriguez",
        avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop",
        rating: 4,
        date: "January 2026",
        comment: "Great location and beautiful villa. The only minor issue was the AC in one bedroom, but it was quickly fixed.",
      },
    ],
  },
  {
    id: 2,
    name: "Modern Tropical Escape",
    location: "Phuket, Thailand",
    description: "A contemporary villa nestled in lush tropical gardens, offering complete privacy and serenity. Features include a private pool, outdoor dining area, and state-of-the-art entertainment system.",
    shortDescription: "Contemporary villa with private pool in tropical gardens",
    price: 380,
    rating: 4.8,
    reviews: 89,
    bedrooms: 4,
    bathrooms: 3,
    guests: 8,
    image: "https://images.unsplash.com/photo-1774552803490-1c95b9020453?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1774552803490-1c95b9020453?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
      "https://images.unsplash.com/photo-1711110065992-6d6aff9ae35c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Garden", "Parking", "TV"],
    host: {
      name: "David Lee",
      avatar: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop",
      joined: "2019",
      description: "Property manager specializing in luxury villas in Southeast Asia.",
    },
    guestReviews: [
      {
        id: 1,
        author: "Sophie Martin",
        avatar: "https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop",
        rating: 5,
        date: "March 2026",
        comment: "Beautiful villa in a peaceful location. The tropical garden was a highlight!",
      },
    ],
  },
  {
    id: 3,
    name: "Sunset Villa Retreat",
    location: "Santorini, Greece",
    description: "Perched on the cliffs of Santorini, this villa offers unparalleled sunset views and traditional Cycladic architecture with modern amenities.",
    shortDescription: "Cliffside villa with spectacular sunset views",
    price: 520,
    rating: 5.0,
    reviews: 156,
    bedrooms: 3,
    bathrooms: 3,
    guests: 6,
    image: "https://images.unsplash.com/photo-1767950470198-c9cd97f8ed87?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1767950470198-c9cd97f8ed87?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Parking", "Hot Tub"],
    host: {
      name: "Maria Papadopoulos",
      avatar: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop",
      joined: "2018",
      description: "Local host passionate about sharing the beauty of Santorini.",
    },
    guestReviews: [
      {
        id: 1,
        author: "Alex Turner",
        avatar: "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop",
        rating: 5,
        date: "March 2026",
        comment: "Dream villa! The sunsets from the terrace are indescribable. Maria was wonderful.",
      },
    ],
  },
  {
    id: 4,
    name: "Palm Paradise Estate",
    location: "Maldives",
    description: "Private island villa surrounded by crystal clear waters and white sandy beaches. Includes water sports equipment and snorkeling gear.",
    shortDescription: "Private island villa with water sports and beach",
    price: 680,
    rating: 4.9,
    reviews: 98,
    bedrooms: 6,
    bathrooms: 5,
    guests: 12,
    image: "https://images.unsplash.com/photo-1760943013869-65a30a4fafd1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1760943013869-65a30a4fafd1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Beach Access", "Parking", "Water Sports", "Garden"],
    host: {
      name: "Ahmed Hassan",
      avatar: "https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?w=150&h=150&fit=crop",
      joined: "2021",
      description: "Luxury property specialist in the Maldives.",
    },
    guestReviews: [],
  },
  {
    id: 5,
    name: "Mountain View Luxury Villa",
    location: "Tuscany, Italy",
    description: "Restored farmhouse with stunning mountain views, vineyard access, and authentic Italian charm combined with modern luxury.",
    shortDescription: "Restored farmhouse with vineyard and mountain views",
    price: 420,
    rating: 4.7,
    reviews: 73,
    bedrooms: 4,
    bathrooms: 4,
    guests: 8,
    image: "https://images.unsplash.com/photo-1711110065954-1c79c1dec505?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1711110065954-1c79c1dec505?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Parking", "Garden", "BBQ"],
    host: {
      name: "Giovanni Rossi",
      avatar: "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&h=150&fit=crop",
      joined: "2017",
      description: "Third generation villa owner, sharing family property with guests.",
    },
    guestReviews: [],
  },
  {
    id: 6,
    name: "Beachfront Oasis Villa",
    location: "Miami, USA",
    description: "Ultra-modern beachfront villa with floor-to-ceiling windows, smart home technology, and direct ocean access.",
    shortDescription: "Ultra-modern beachfront with smart home features",
    price: 550,
    rating: 4.8,
    reviews: 112,
    bedrooms: 5,
    bathrooms: 4,
    guests: 10,
    image: "https://images.unsplash.com/photo-1760067537640-6ffab10b27d2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1760067537640-6ffab10b27d2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Beach Access", "Parking", "Gym", "Smart Home"],
    host: {
      name: "Jessica Martinez",
      avatar: "https://images.unsplash.com/photo-1489424731084-a5d8b219a5bb?w=150&h=150&fit=crop",
      joined: "2022",
      description: "Real estate professional managing premium Miami properties.",
    },
    guestReviews: [],
  },
  {
    id: 7,
    name: "Zen Garden Villa",
    location: "Kyoto, Japan",
    description: "Traditional Japanese villa with zen garden, hot spring bath, and authentic tatami rooms blended with modern comfort.",
    shortDescription: "Traditional villa with zen garden and hot spring",
    price: 390,
    rating: 4.9,
    reviews: 84,
    bedrooms: 3,
    bathrooms: 2,
    guests: 6,
    image: "https://images.unsplash.com/photo-1711110066231-cb235d6e117e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1711110066231-cb235d6e117e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["WiFi", "AC", "Kitchen", "Parking", "Garden", "Hot Spring", "Traditional Bath"],
    host: {
      name: "Yuki Tanaka",
      avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop",
      joined: "2019",
      description: "Cultural ambassador sharing traditional Japanese hospitality.",
    },
    guestReviews: [],
  },
  {
    id: 8,
    name: "Desert Luxury Retreat",
    location: "Dubai, UAE",
    description: "Contemporary villa in exclusive community with private pool, cinema room, and 24/7 concierge service.",
    shortDescription: "Contemporary villa with cinema and concierge service",
    price: 720,
    rating: 4.8,
    reviews: 67,
    bedrooms: 7,
    bathrooms: 6,
    guests: 14,
    image: "https://images.unsplash.com/photo-1760067537204-fe9b55b2f1b0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    gallery: [
      "https://images.unsplash.com/photo-1760067537204-fe9b55b2f1b0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080",
    ],
    amenities: ["Pool", "WiFi", "AC", "Kitchen", "Parking", "Gym", "Cinema", "Concierge"],
    host: {
      name: "Omar Al-Rashid",
      avatar: "https://images.unsplash.com/photo-1463453091185-61582044d556?w=150&h=150&fit=crop",
      joined: "2020",
      description: "Luxury property portfolio manager in Dubai.",
    },
    guestReviews: [],
  },
];
