import { useState } from "react";
import { useParams, Link } from "react-router";
import { ArrowLeft, Star, MapPin, Users, BedDouble, Bath, Wifi, Droplet, Wind, UtensilsCrossed, Car, Home, Waves, TreePalm, Flame } from "lucide-react";
import { DayPicker } from "react-day-picker";
import { villas } from "../data/villas";

export default function VillaDetail() {
  const { id } = useParams();
  const villa = villas.find((v) => v.id === Number(id));
  const [selectedImage, setSelectedImage] = useState(0);
  const [checkIn, setCheckIn] = useState<Date>();
  const [checkOut, setCheckOut] = useState<Date>();
  const [guests, setGuests] = useState(2);

  if (!villa) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl mb-4">Villa not found</h2>
          <Link to="/" className="text-blue-600 hover:underline">
            Back to search
          </Link>
        </div>
      </div>
    );
  }

  const amenityIcons: Record<string, any> = {
    Pool: Droplet,
    WiFi: Wifi,
    AC: Wind,
    Kitchen: UtensilsCrossed,
    Parking: Car,
    "Beach Access": Waves,
    Garden: TreePalm,
    BBQ: Flame,
  };

  const calculateTotal = () => {
    if (!checkIn || !checkOut) return 0;
    const nights = Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
    return nights > 0 ? nights * villa.price : 0;
  };

  const nights = checkIn && checkOut
    ? Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24))
    : 0;
  const total = calculateTotal();
  const serviceFee = total * 0.1;
  const cleaningFee = 50;
  const grandTotal = total + serviceFee + cleaningFee;

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
        <div className="max-w-[1400px] mx-auto flex items-center gap-4">
          <Link to="/" className="flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <ArrowLeft className="w-5 h-5" />
            <span>Back to search</span>
          </Link>
          <div className="flex items-center gap-3 ml-auto">
            <Home className="w-8 h-8 text-blue-600" />
            <h1 className="text-xl">VillaStay</h1>
          </div>
        </div>
      </header>

      <div className="max-w-[1400px] mx-auto p-6">
        {/* Title and Rating */}
        <div className="mb-6">
          <h1 className="text-3xl mb-2">{villa.name}</h1>
          <div className="flex items-center gap-4 text-gray-600">
            <div className="flex items-center gap-1">
              <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
              <span>{villa.rating}</span>
              <span>({villa.reviews} reviews)</span>
            </div>
            <div className="flex items-center gap-1">
              <MapPin className="w-5 h-5" />
              <span>{villa.location}</span>
            </div>
          </div>
        </div>

        <div className="flex gap-6 mb-8">
          {/* Gallery */}
          <div className="flex-1">
            <div className="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-200">
              <img
                src={villa.gallery[selectedImage]}
                alt={villa.name}
                className="w-full h-[500px] object-cover"
              />
              <div className="p-4 flex gap-3 overflow-x-auto">
                {villa.gallery.map((img, index) => (
                  <button
                    key={index}
                    onClick={() => setSelectedImage(index)}
                    className={`shrink-0 rounded-lg overflow-hidden border-2 transition-all ${
                      selectedImage === index
                        ? "border-blue-600 scale-105"
                        : "border-transparent opacity-60 hover:opacity-100"
                    }`}
                  >
                    <img src={img} alt={`Gallery ${index + 1}`} className="w-24 h-20 object-cover" />
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Booking Widget */}
          <aside className="w-96 shrink-0">
            <div className="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sticky top-24">
              <div className="mb-6">
                <div className="flex items-baseline gap-2 mb-1">
                  <span className="text-3xl">${villa.price}</span>
                  <span className="text-gray-600">/ night</span>
                </div>
                <div className="flex items-center gap-1 text-sm text-gray-600">
                  <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                  <span>{villa.rating}</span>
                  <span>({villa.reviews} reviews)</span>
                </div>
              </div>

              <div className="space-y-4 mb-6">
                <div>
                  <label className="block text-sm mb-2">Check-in / Check-out</label>
                  <div className="border border-gray-300 rounded-lg p-3">
                    <DayPicker
                      mode="range"
                      selected={{ from: checkIn, to: checkOut }}
                      onSelect={(range) => {
                        setCheckIn(range?.from);
                        setCheckOut(range?.to);
                      }}
                      disabled={{ before: new Date() }}
                      className="rdp-custom"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm mb-2">Guests</label>
                  <div className="flex items-center gap-3 border border-gray-300 rounded-lg p-3">
                    <Users className="w-5 h-5 text-gray-400" />
                    <select
                      value={guests}
                      onChange={(e) => setGuests(Number(e.target.value))}
                      className="flex-1 outline-none bg-transparent"
                    >
                      {Array.from({ length: villa.guests }, (_, i) => i + 1).map((num) => (
                        <option key={num} value={num}>
                          {num} {num === 1 ? "guest" : "guests"}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>

              {total > 0 && (
                <div className="space-y-3 mb-6 pb-6 border-b border-gray-200">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">
                      ${villa.price} x {nights} {nights === 1 ? "night" : "nights"}
                    </span>
                    <span>${total}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Service fee</span>
                    <span>${serviceFee.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Cleaning fee</span>
                    <span>${cleaningFee}</span>
                  </div>
                </div>
              )}

              {total > 0 && (
                <div className="flex justify-between mb-6">
                  <span>Total</span>
                  <span className="text-xl">${grandTotal.toFixed(2)}</span>
                </div>
              )}

              <button className="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {total > 0 ? "Reserve" : "Check availability"}
              </button>
            </div>
          </aside>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-8">
            {/* Property Info */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div className="flex items-center gap-6 mb-6 pb-6 border-b border-gray-200">
                <div className="flex items-center gap-2">
                  <Users className="w-5 h-5 text-gray-600" />
                  <span>{villa.guests} guests</span>
                </div>
                <div className="flex items-center gap-2">
                  <BedDouble className="w-5 h-5 text-gray-600" />
                  <span>{villa.bedrooms} bedrooms</span>
                </div>
                <div className="flex items-center gap-2">
                  <Bath className="w-5 h-5 text-gray-600" />
                  <span>{villa.bathrooms} bathrooms</span>
                </div>
              </div>

              <div>
                <h2 className="text-xl mb-4">About this villa</h2>
                <p className="text-gray-700 leading-relaxed">{villa.description}</p>
              </div>
            </div>

            {/* Amenities */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h2 className="text-xl mb-4">Amenities</h2>
              <div className="grid grid-cols-2 gap-4">
                {villa.amenities.map((amenity) => {
                  const Icon = amenityIcons[amenity] || Home;
                  return (
                    <div key={amenity} className="flex items-center gap-3">
                      <Icon className="w-5 h-5 text-gray-600" />
                      <span>{amenity}</span>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Reviews */}
            {villa.guestReviews.length > 0 && (
              <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div className="flex items-center gap-2 mb-6">
                  <Star className="w-6 h-6 fill-yellow-400 text-yellow-400" />
                  <h2 className="text-xl">
                    {villa.rating} · {villa.reviews} reviews
                  </h2>
                </div>
                <div className="space-y-6">
                  {villa.guestReviews.map((review) => (
                    <div key={review.id} className="pb-6 border-b border-gray-200 last:border-0 last:pb-0">
                      <div className="flex items-start gap-4">
                        <img
                          src={review.avatar}
                          alt={review.author}
                          className="w-12 h-12 rounded-full object-cover"
                        />
                        <div className="flex-1">
                          <div className="flex items-center justify-between mb-2">
                            <div>
                              <h4>{review.author}</h4>
                              <p className="text-sm text-gray-600">{review.date}</p>
                            </div>
                            <div className="flex items-center gap-1">
                              {Array.from({ length: review.rating }).map((_, i) => (
                                <Star key={i} className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                              ))}
                            </div>
                          </div>
                          <p className="text-gray-700">{review.comment}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Host Info */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
              <h2 className="text-xl mb-4">Hosted by {villa.host.name}</h2>
              <div className="flex items-center gap-4 mb-4">
                <img
                  src={villa.host.avatar}
                  alt={villa.host.name}
                  className="w-16 h-16 rounded-full object-cover"
                />
                <div>
                  <p className="text-sm text-gray-600">Joined in {villa.host.joined}</p>
                  <div className="flex items-center gap-1 text-sm">
                    <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                    <span>{villa.reviews} reviews</span>
                  </div>
                </div>
              </div>
              <p className="text-gray-700 mb-4">{villa.host.description}</p>
              <button className="w-full py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Contact Host
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
