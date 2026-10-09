import { useState } from "react";
import { Link } from "react-router";
import { Star, Grid3x3, List, Wifi, Droplet, Wind, UtensilsCrossed, Car, Home, Waves, TreePalm } from "lucide-react";
import * as Slider from "@radix-ui/react-slider";
import * as Checkbox from "@radix-ui/react-checkbox";
import { villas } from "../data/villas";

export default function SearchResults() {
  const [priceRange, setPriceRange] = useState([0, 1000]);
  const [bedrooms, setBedrooms] = useState<number | null>(null);
  const [rating, setRating] = useState<number | null>(null);
  const [selectedAmenities, setSelectedAmenities] = useState<string[]>([]);
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");

  const amenitiesList = [
    { id: "pool", label: "Pool", icon: Droplet },
    { id: "wifi", label: "WiFi", icon: Wifi },
    { id: "ac", label: "AC", icon: Wind },
    { id: "kitchen", label: "Kitchen", icon: UtensilsCrossed },
    { id: "parking", label: "Parking", icon: Car },
    { id: "beach", label: "Beach Access", icon: Waves },
  ];

  const toggleAmenity = (amenityId: string) => {
    setSelectedAmenities((prev) =>
      prev.includes(amenityId) ? prev.filter((a) => a !== amenityId) : [...prev, amenityId]
    );
  };

  const filteredVillas = villas.filter((villa) => {
    const matchesPrice = villa.price >= priceRange[0] && villa.price <= priceRange[1];
    const matchesBedrooms = bedrooms === null || villa.bedrooms >= bedrooms;
    const matchesRating = rating === null || villa.rating >= rating;
    const matchesAmenities =
      selectedAmenities.length === 0 ||
      selectedAmenities.every((amenity) =>
        villa.amenities.some((va) => va.toLowerCase().includes(amenity))
      );

    return matchesPrice && matchesBedrooms && matchesRating && matchesAmenities;
  });

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
        <div className="max-w-[1600px] mx-auto flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Home className="w-8 h-8 text-blue-600" />
            <h1 className="text-xl">boboinvilla</h1>
          </div>
          <div className="flex items-center gap-4">
            <span className="text-sm text-gray-600">{filteredVillas.length} villas found</span>
            <div className="flex gap-2 border border-gray-300 rounded-lg p-1">
              <button
                onClick={() => setViewMode("grid")}
                className={`p-2 rounded ${viewMode === "grid" ? "bg-gray-200" : ""}`}
              >
                <Grid3x3 className="w-4 h-4" />
              </button>
              <button
                onClick={() => setViewMode("list")}
                className={`p-2 rounded ${viewMode === "list" ? "bg-gray-200" : ""}`}
              >
                <List className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </header>

      <div className="max-w-[1600px] mx-auto flex gap-6 p-6">
        {/* Sidebar */}
        <aside className="w-80 shrink-0 sticky top-24 h-fit">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <h2 className="text-lg">Filters</h2>

            {/* Price Range */}
            <div className="space-y-3">
              <label className="block text-sm">Price Range (per night)</label>
              <Slider.Root
                className="relative flex items-center select-none touch-none w-full h-5"
                value={priceRange}
                onValueChange={setPriceRange}
                max={1000}
                step={10}
                minStepsBetweenThumbs={1}
              >
                <Slider.Track className="bg-gray-200 relative grow rounded-full h-1">
                  <Slider.Range className="absolute bg-blue-600 rounded-full h-full" />
                </Slider.Track>
                <Slider.Thumb className="block w-4 h-4 bg-white border-2 border-blue-600 rounded-full hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <Slider.Thumb className="block w-4 h-4 bg-white border-2 border-blue-600 rounded-full hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </Slider.Root>
              <div className="flex justify-between text-sm text-gray-600">
                <span>${priceRange[0]}</span>
                <span>${priceRange[1]}</span>
              </div>
            </div>

            {/* Bedrooms */}
            <div className="space-y-3">
              <label className="block text-sm">Minimum Bedrooms</label>
              <div className="flex gap-2">
                {[1, 2, 3, 4, 5].map((num) => (
                  <button
                    key={num}
                    onClick={() => setBedrooms(bedrooms === num ? null : num)}
                    className={`flex-1 py-2 px-3 rounded-lg border transition-colors ${
                      bedrooms === num
                        ? "bg-blue-600 text-white border-blue-600"
                        : "border-gray-300 hover:border-gray-400"
                    }`}
                  >
                    {num}
                  </button>
                ))}
              </div>
            </div>

            {/* Amenities */}
            <div className="space-y-3">
              <label className="block text-sm">Amenities</label>
              <div className="space-y-2">
                {amenitiesList.map((amenity) => {
                  const Icon = amenity.icon;
                  return (
                    <div key={amenity.id} className="flex items-center gap-3">
                      <Checkbox.Root
                        id={amenity.id}
                        checked={selectedAmenities.includes(amenity.id)}
                        onCheckedChange={() => toggleAmenity(amenity.id)}
                        className="flex items-center justify-center w-5 h-5 border border-gray-300 rounded bg-white data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600"
                      >
                        <Checkbox.Indicator>
                          <svg
                            width="15"
                            height="15"
                            viewBox="0 0 15 15"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                          >
                            <path
                              d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z"
                              fill="currentColor"
                              fillRule="evenodd"
                              clipRule="evenodd"
                            />
                          </svg>
                        </Checkbox.Indicator>
                      </Checkbox.Root>
                      <label
                        htmlFor={amenity.id}
                        className="flex items-center gap-2 cursor-pointer select-none"
                      >
                        <Icon className="w-4 h-4 text-gray-500" />
                        <span className="text-sm">{amenity.label}</span>
                      </label>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Star Rating */}
            <div className="space-y-3">
              <label className="block text-sm">Minimum Rating</label>
              <div className="flex flex-col gap-2">
                {[5, 4, 3].map((num) => (
                  <button
                    key={num}
                    onClick={() => setRating(rating === num ? null : num)}
                    className={`flex items-center gap-2 py-2 px-3 rounded-lg border transition-colors ${
                      rating === num
                        ? "bg-blue-600 text-white border-blue-600"
                        : "border-gray-300 hover:border-gray-400"
                    }`}
                  >
                    {Array.from({ length: num }).map((_, i) => (
                      <Star key={i} className="w-4 h-4 fill-current" />
                    ))}
                    <span className="text-sm">& up</span>
                  </button>
                ))}
              </div>
            </div>

            {/* Reset */}
            <button
              onClick={() => {
                setPriceRange([0, 1000]);
                setBedrooms(null);
                setRating(null);
                setSelectedAmenities([]);
              }}
              className="w-full py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Reset Filters
            </button>
          </div>
        </aside>

        {/* Main Content */}
        <main className="flex-1">
          <div
            className={
              viewMode === "grid"
                ? "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                : "flex flex-col gap-4"
            }
          >
            {filteredVillas.map((villa) => (
              <Link
                key={villa.id}
                to={`/villa/${villa.id}`}
                className={`bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow ${
                  viewMode === "list" ? "flex" : ""
                }`}
              >
                <img
                  src={villa.image}
                  alt={villa.name}
                  className={
                    viewMode === "grid"
                      ? "w-full h-56 object-cover"
                      : "w-72 h-full object-cover"
                  }
                />
                <div className="p-5 flex-1 flex flex-col">
                  <div className="flex items-start justify-between gap-2 mb-2">
                    <div>
                      <h3 className="text-lg mb-1">{villa.name}</h3>
                      <p className="text-sm text-gray-600">{villa.location}</p>
                    </div>
                    <div className="flex items-center gap-1 text-sm">
                      <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                      <span>{villa.rating}</span>
                      <span className="text-gray-500">({villa.reviews})</span>
                    </div>
                  </div>
                  <p className="text-sm text-gray-600 mb-3 line-clamp-2">
                    {villa.shortDescription}
                  </p>
                  <div className="flex items-center gap-4 text-sm text-gray-600 mb-4">
                    <span>{villa.bedrooms} Beds</span>
                    <span>•</span>
                    <span>{villa.bathrooms} Baths</span>
                    <span>•</span>
                    <span>{villa.guests} Guests</span>
                  </div>
                  <div className="mt-auto flex items-center justify-between">
                    <div>
                      <span className="text-2xl">${villa.price}</span>
                      <span className="text-sm text-gray-600"> / night</span>
                    </div>
                    <button className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                      Book Now
                    </button>
                  </div>
                </div>
              </Link>
            ))}
          </div>

          {filteredVillas.length === 0 && (
            <div className="text-center py-16">
              <TreePalm className="w-16 h-16 text-gray-300 mx-auto mb-4" />
              <h3 className="text-xl text-gray-600 mb-2">No villas found</h3>
              <p className="text-gray-500">Try adjusting your filters</p>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}
