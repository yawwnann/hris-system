// Shared style tokens for the authentication pages (Login, Register, ForgotPassword).
// Only Tailwind classes already used elsewhere in the app (orange actions, emerald
// brand green, gray and zinc neutrals) so the auth pages stay consistent with the product.

export const authInputClass =
  "h-11 rounded-xl border-gray-200 bg-gray-50 px-4 text-base text-gray-900 transition-colors placeholder:text-gray-400 focus-visible:border-orange-500 focus-visible:ring-2 focus-visible:ring-orange-500/30 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500";

export const authLabelClass = "text-sm font-medium text-gray-700 dark:text-gray-300";

export const authPrimaryButtonClass =
  "h-12 w-full rounded-xl bg-orange-600 text-base font-semibold text-white shadow-lg shadow-orange-600/25 transition-all hover:bg-orange-700 focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white active:scale-[0.98] motion-reduce:transition-none motion-reduce:active:scale-100 dark:focus-visible:ring-offset-zinc-950";

// orange-700 (not 600) keeps 4.5:1 AA contrast for small link text on white.
export const authLinkClass =
  "rounded-sm font-semibold text-orange-700 transition-colors hover:text-orange-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40 dark:text-orange-400 dark:hover:text-orange-300";

// w-11 inside an h-11 input gives the toggle a 44x44 hit area.
export const authEyeButtonClass =
  "absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-gray-400 transition-colors hover:text-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40 dark:hover:text-gray-300";

export const authIconChipClass =
  "mb-6 flex size-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400";

export const authSuccessChipClass =
  "mx-auto mb-6 flex size-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400";

export const authErrorClass =
  "flex items-start gap-2.5 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-600 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400";

export const authHeadingClass =
  "text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl";

export const authLeadClass = "mt-2 text-base leading-relaxed text-gray-500 dark:text-zinc-400";

export const authEnterClass =
  "animate-in fade-in slide-in-from-bottom-4 duration-500 motion-reduce:animate-none";
