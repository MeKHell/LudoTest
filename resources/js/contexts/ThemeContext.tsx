import { createContext, useContext, createSignal, onMount, onCleanup, ParentComponent } from 'solid-js'

interface ThemeContextValue {
  isDark: () => boolean
  toggleTheme: () => void
}

const ThemeContext = createContext<ThemeContextValue>()

export const ThemeProvider: ParentComponent = (props) => {
  const [isDark, setIsDark] = createSignal(false)

  onMount(() => {
    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme')
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    
    const shouldBeDark = savedTheme === 'dark' || (savedTheme === null && systemPrefersDark)
    setIsDark(shouldBeDark)
    
    if (shouldBeDark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }

    // Listen for changes in system preference (only if no saved preference)
    if (savedTheme === null) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      const handleChange = (e: MediaQueryListEvent) => {
        setIsDark(e.matches)
        if (e.matches) {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }
      }
      mediaQuery.addEventListener('change', handleChange)
      
      // Cleanup
      onCleanup(() => mediaQuery.removeEventListener('change', handleChange))
    }
  })

  const toggleTheme = () => {
    const newDarkState = !isDark()
    setIsDark(newDarkState)
    
    if (newDarkState) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
    
    // Save preference
    localStorage.setItem('theme', newDarkState ? 'dark' : 'light')
  }

  return (
    <ThemeContext.Provider value={{ isDark, toggleTheme }}>
      {props.children}
    </ThemeContext.Provider>
  )
}

export const useTheme = () => {
  const context = useContext(ThemeContext)
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider')
  }
  return context
}