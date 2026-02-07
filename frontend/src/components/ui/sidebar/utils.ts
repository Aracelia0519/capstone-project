import type { ComputedRef, Ref } from "vue"
import { createContext } from "reka-ui"

export const SIDEBAR_COOKIE_NAME = "sidebar_state"
export const SIDEBAR_COOKIE_MAX_AGE = 60 * 60 * 24 * 7

// The expanded width of the sidebar
export const SIDEBAR_WIDTH = "16rem"
// The width of the sidebar on mobile devices
export const SIDEBAR_WIDTH_MOBILE = "18rem"
// Increased to 4rem for a smoother, more balanced look in "icon" mode
export const SIDEBAR_WIDTH_ICON = "4rem" 

export const SIDEBAR_KEYBOARD_SHORTCUT = "b"

/**
 * Shared transition timing for a "super smooth" feel.
 * Using cubic-bezier(0.4, 0, 0.2, 1) ensures the movement 
 * starts fast and ends with a silky deceleration.
 */
export const SIDEBAR_TRANSITION = "500ms cubic-bezier(0.4, 0, 0.2, 1)"

export const [useSidebar, provideSidebarContext] = createContext<{
  state: ComputedRef<"expanded" | "collapsed">
  open: Ref<boolean>
  setOpen: (value: boolean) => void
  isMobile: Ref<boolean>
  openMobile: Ref<boolean>
  setOpenMobile: (value: boolean) => void
  toggleSidebar: () => void
}>("Sidebar")