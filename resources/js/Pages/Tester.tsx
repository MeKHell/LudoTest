import { useTheme } from '@/contexts/ThemeContext'
import GuestLayout from '@/Layouts/GuestLayout'

export default function Tester() {
  const { isDark } = useTheme()
  return <div>{isDark() ? 'Dark' : 'Light'}</div>
}

Tester.layout = GuestLayout
