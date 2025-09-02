import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { TextField, TextFieldRoot } from "@/components/ui/textfield";
import { useI18n } from "@/context/i18nContext";
import Clock from "lucide-solid/icons/clock";
import Users from "lucide-solid/icons/users";
import Search from "lucide-solid/icons/search";
import Star from "lucide-solid/icons/star";
import { createSignal, For } from "solid-js";
import { Badge } from "@/components/ui/badge";
import TrendingUp from "lucide-solid/icons/trending-up";
import Spade from "lucide-solid/icons/spade";
const featuredGames = [
  {
    id: 1,
    title: "Wingspan",
    image: "/wingspan-board-game-box.png",
    rating: 4.8,
    reviews: 1247,
    players: "1-5",
    playTime: "40-70 min",
    complexity: 2.4,
    description:
      "A competitive, medium-weight, card-driven, engine-building board game.",
  },
  {
    id: 2,
    title: "Azul",
    image: "/azul-board-game-colorful-tiles.png",
    rating: 4.6,
    reviews: 892,
    players: "2-4",
    playTime: "30-45 min",
    complexity: 1.8,
    description:
      "A tile-placement game where players compete to create beautiful patterns.",
  },
  {
    id: 3,
    title: "Gloomhaven",
    image: "/gloomhaven-fantasy-board-game.png",
    rating: 4.9,
    reviews: 2156,
    players: "1-4",
    playTime: "60-120 min",
    complexity: 3.8,
    description:
      "A game of Euro-inspired tactical combat in a persistent world.",
  },
];

const popularGames = [
  { title: "Ticket to Ride", rating: 4.5, trend: "+12%" },
  { title: "Catan", rating: 4.3, trend: "+8%" },
  { title: "Pandemic", rating: 4.7, trend: "+15%" },
  { title: "7 Wonders", rating: 4.4, trend: "+6%" },
  { title: "Splendor", rating: 4.2, trend: "+10%" },
];

export default function HomePage() {
  const [searchQuery, setSearchQuery] = createSignal("");
  const { t } = useI18n();
  return (
    <div class="min-h-screen bg-background">
      {/* Hero Section */}
      <section class="py-20 px-4">
        <div class="container mx-auto text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-balance mb-6">
            {t("title")}
          </h1>
          <p class="text-xl text-muted-foreground text-balance mb-8 max-w-2xl mx-auto">
            {t("subtitle")}
          </p>

          {/* Search Bar */}
          <div class="max-w-md mx-auto mb-12">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <TextFieldRoot>
                <TextField
                  type="text"
                  placeholder={t("search.placeholder")}
                  value={searchQuery()}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  class="pl-10 h-12 text-lg"
                />
              </TextFieldRoot>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Games */}
      <section class="py-16 px-4 bg-muted/30">
        <div class="container mx-auto">
          <h2 class="text-3xl font-bold text-center mb-12">{t("featured")}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <For each={featuredGames}>
              {(game) => (
                <Card class="overflow-hidden hover:shadow-lg transition-shadow">
                  <div class="aspect-video overflow-hidden">
                    <img
                      src={game.image || "/placeholder.svg"}
                      alt={game.title}
                      class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                  <CardHeader>
                    <div class="flex items-center justify-between">
                      <CardTitle class="text-xl">{game.title}</CardTitle>
                      <div class="flex items-center gap-1">
                        <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                        <span class="font-semibold">{game.rating}</span>
                      </div>
                    </div>
                    <CardDescription>{game.description}</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div class="flex items-center justify-between text-sm text-muted-foreground mb-4">
                      <div class="flex items-center gap-1">
                        <Users class="h-4 w-4" />
                        {game.players}
                      </div>
                      <div class="flex items-center gap-1">
                        <Clock class="h-4 w-4" />
                        {game.playTime}
                      </div>
                      <Badge variant="secondary">
                        Complexity: {game.complexity}/5
                      </Badge>
                    </div>
                    <div class="text-sm text-muted-foreground">
                      {game.reviews} {t("reviews")}
                    </div>
                  </CardContent>
                </Card>
              )}
            </For>
          </div>
        </div>
      </section>

      {/* Popular This Week */}
      <section class="py-16 px-4">
        <div class="container mx-auto">
          <h2 class="text-3xl font-bold text-center mb-12">{t("popular")}</h2>

          <div class="max-w-2xl mx-auto">
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <TrendingUp class="h-5 w-5 text-primary" />
                  Trending Games
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <For each={popularGames}>
                    {(game, index) => (
                      <div class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-3">
                          <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-semibold">
                            {index() + 1}
                          </div>
                          <div>
                            <div class="font-medium">{game.title}</div>
                            <div class="flex items-center gap-1 text-sm text-muted-foreground">
                              <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                              {game.rating}
                            </div>
                          </div>
                        </div>
                        <Badge
                          variant="outline"
                          class="text-green-600 border-green-600"
                        >
                          {game.trend}
                        </Badge>
                      </div>
                    )}
                  </For>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer class="bg-card border-t py-12 px-4">
        <div class="container mx-auto">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
              <div class="flex items-center gap-2 mb-4">
                <Spade class="h-6 w-6 text-primary" />
                <span class="text-lg font-bold text-primary">LudoTest</span>
              </div>
              <p class="text-muted-foreground">
                The ultimate platform for board game enthusiasts to discover,
                rate, and review games.
              </p>
            </div>

            <div>
              <h3 class="font-semibold mb-4">Platform</h3>
              <ul class="space-y-2 text-muted-foreground">
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Browse Games
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Top Rated
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    New Releases
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Categories
                  </a>
                </li>
              </ul>
            </div>

            <div>
              <h3 class="font-semibold mb-4">Community</h3>
              <ul class="space-y-2 text-muted-foreground">
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Forums
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Reviews
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Events
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Blog
                  </a>
                </li>
              </ul>
            </div>

            <div>
              <h3 class="font-semibold mb-4">Support</h3>
              <ul class="space-y-2 text-muted-foreground">
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Help Center
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Contact Us
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="#" class="hover:text-foreground transition-colors">
                    Terms of Service
                  </a>
                </li>
              </ul>
            </div>
          </div>

          <div class="border-t mt-8 pt-8 text-center text-muted-foreground">
            <p>&copy; 2025 LudoTest. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
