import GuestLayout from '@/Layouts/GuestLayout'
import { createSignal, For, JSX } from 'solid-js'
import Clock from 'lucide-solid/icons/clock'
import Users from 'lucide-solid/icons/users'
import Search from 'lucide-solid/icons/search'
import Star from 'lucide-solid/icons/star'
import TrendingUp from 'lucide-solid/icons/trending-up'
import Spade from 'lucide-solid/icons/spade'
import { TextField, TextFieldRoot } from '@/components/ui/textfield'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { useLang } from '@/hooks/useLang'
const featuredGames = [
  {
    id: 1,
    title: 'Wingspan',
    image: '/wingspan-board-game-box.png',
    rating: 4.8,
    reviews: 1247,
    players: '1-5',
    playTime: '40-70 min',
    complexity: 2.4,
    description: 'A competitive, medium-weight, card-driven, engine-building board game.',
  },
  {
    id: 2,
    title: 'Azul',
    image: '/azul-board-game-colorful-tiles.png',
    rating: 4.6,
    reviews: 892,
    players: '2-4',
    playTime: '30-45 min',
    complexity: 1.8,
    description: 'A tile-placement game where players compete to create beautiful patterns.',
  },
  {
    id: 3,
    title: 'Gloomhaven',
    image: '/gloomhaven-fantasy-board-game.png',
    rating: 4.9,
    reviews: 2156,
    players: '1-4',
    playTime: '60-120 min',
    complexity: 3.8,
    description: 'A game of Euro-inspired tactical combat in a persistent world.',
  },
]

const popularGames = [
  { title: 'Ticket to Ride', rating: 4.5, trend: '+12%' },
  { title: 'Catan', rating: 4.3, trend: '+8%' },
  { title: 'Pandemic', rating: 4.7, trend: '+15%' },
  { title: '7 Wonders', rating: 4.4, trend: '+6%' },
  { title: 'Splendor', rating: 4.2, trend: '+10%' },
]

export default function Welcome() {
  const [searchQuery, setSearchQuery] = createSignal('')
  const { trans: t } = useLang()
  return (
    <div class="bg-background min-h-screen">
      {/* Hero Section */}
      <section class="px-4 py-20">
        <div class="container mx-auto text-center">
          <h1 class="mb-6 text-4xl font-bold text-balance md:text-6xl">{t('welcome.title')}</h1>
          <p class="text-muted-foreground mx-auto mb-8 max-w-2xl text-xl text-balance">
            {t('welcome.subtitle')}
          </p>

          {/* Search Bar */}
          <div class="mx-auto mb-12 max-w-md">
            <div class="relative">
              <Search class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
              <TextFieldRoot>
                <TextField
                  type="text"
                  placeholder={t('welcome.placeholder')}
                  value={searchQuery()}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  class="h-12 pl-10 text-lg"
                />
              </TextFieldRoot>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Games */}
      <section class="bg-muted/30 px-4 py-16">
        <div class="container mx-auto">
          <h2 class="mb-12 text-center text-3xl font-bold">{t('welcome.featured')}</h2>

          <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <For each={featuredGames}>
              {(game) => (
                <Card class="overflow-hidden transition-shadow hover:shadow-lg">
                  <div class="aspect-video overflow-hidden">
                    <img
                      src={game.image || '/placeholder.svg'}
                      alt={game.title}
                      class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
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
                    <div class="text-muted-foreground mb-4 flex items-center justify-between text-sm">
                      <div class="flex items-center gap-1">
                        <Users class="h-4 w-4" />
                        {game.players}
                      </div>
                      <div class="flex items-center gap-1">
                        <Clock class="h-4 w-4" />
                        {game.playTime}
                      </div>
                      <Badge variant="secondary">Complexity: {game.complexity}/5</Badge>
                    </div>
                    <div class="text-muted-foreground text-sm">
                      {game.reviews} {t('welcome.reviews')}
                    </div>
                  </CardContent>
                </Card>
              )}
            </For>
          </div>
        </div>
      </section>

      {/* Popular This Week */}
      <section class="px-4 py-16">
        <div class="container mx-auto">
          <h2 class="mb-12 text-center text-3xl font-bold">{t('welcome.popular')}</h2>

          <div class="mx-auto max-w-2xl">
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <TrendingUp class="text-primary h-5 w-5" />
                  Trending Games
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <For each={popularGames}>
                    {(game, index) => (
                      <div class="hover:bg-muted/50 flex items-center justify-between rounded-lg p-3 transition-colors">
                        <div class="flex items-center gap-3">
                          <div class="bg-primary text-primary-foreground flex h-8 w-8 items-center justify-center rounded-full font-semibold">
                            {index() + 1}
                          </div>
                          <div>
                            <div class="font-medium">{game.title}</div>
                            <div class="text-muted-foreground flex items-center gap-1 text-sm">
                              <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                              {game.rating}
                            </div>
                          </div>
                        </div>
                        <Badge variant="outline" class="border-green-600 text-green-600">
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
      <footer class="bg-card border-t px-4 py-12">
        <div class="container mx-auto">
          <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            <div>
              <div class="mb-4 flex items-center gap-2">
                <Spade class="text-primary h-6 w-6" />
                <span class="text-primary text-lg font-bold">LudoTest</span>
              </div>
              <p class="text-muted-foreground">
                The ultimate platform for board game enthusiasts to discover, rate, and review
                games.
              </p>
            </div>

            <div>
              <h3 class="mb-4 font-semibold">Platform</h3>
              <ul class="text-muted-foreground space-y-2">
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
              <h3 class="mb-4 font-semibold">Community</h3>
              <ul class="text-muted-foreground space-y-2">
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
              <h3 class="mb-4 font-semibold">Support</h3>
              <ul class="text-muted-foreground space-y-2">
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

          <div class="text-muted-foreground mt-8 border-t pt-8 text-center">
            <p>&copy; 2025 LudoTest. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}

Welcome.layout = (props: { children: Element }) => (
  <GuestLayout title="Welcome">{props.children}</GuestLayout>
)
