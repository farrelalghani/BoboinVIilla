import { Header } from './components/Header';
import { Hero } from './components/Hero';
import { FeaturedVillas } from './components/FeaturedVillas';
import { BecomeHost } from './components/BecomeHost';
import { Footer } from './components/Footer';

export default function App() {
  return (
    <div className="min-h-screen bg-white">
      <Header />
      <Hero />
      <FeaturedVillas />
      <BecomeHost />
      <Footer />
    </div>
  );
}