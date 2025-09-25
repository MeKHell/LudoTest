import { useTheme } from '@/contexts/ThemeContext'
import GuestLayout from '@/Layouts/GuestLayout'

export default function Tester() {
  const { isDark } = useTheme()
  return <div class="mt-10 ml-3">{isDark() ? 'Dark' : 'Light'}</div>
}

Tester.layout = GuestLayout
