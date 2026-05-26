/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Brand — calm, slightly desaturated blue. The treasure-map blue.
        brand: {
          50:  '#f0f6ff',
          100: '#dceaff',
          200: '#b8d4ff',
          300: '#8cb8ff',
          400: '#5e95f5',
          500: '#3b73e6',
          600: '#2a58c4',
          700: '#22459a',
          800: '#1d3a7c',
          900: '#172e62',
          950: '#0e1c3e',
        },
        // Neutrals — warmer than slate, cooler than stone. Reads as "paper".
        neutral: {
          50:  '#fafaf7',
          100: '#f4f3ee',
          200: '#e8e6df',
          300: '#d2cfc4',
          400: '#a8a497',
          500: '#7c7869',
          600: '#5b584d',
          700: '#46443c',
          800: '#2f2e29',
          900: '#1b1a17',
          950: '#0d0c0a',
        },
        // Semantic
        success: {
          50:  '#ecfdf3',
          100: '#d1fadf',
          500: '#16a34a',
          600: '#15803d',
          700: '#166534',
        },
        warn: {
          50:  '#fffbeb',
          100: '#fef3c7',
          500: '#d97706',
          600: '#b45309',
          700: '#92400e',
        },
        danger: {
          50:  '#fef2f2',
          100: '#fee2e2',
          500: '#dc2626',
          600: '#b91c1c',
          700: '#991b1b',
        },
        info: {
          50:  '#eff6ff',
          100: '#dbeafe',
          500: '#2563eb',
          600: '#1d4ed8',
          700: '#1e40af',
        },
      },
      fontFamily: {
        sans: ['"Inter"', '"Segoe UI"', 'system-ui', 'sans-serif'],
        display: ['"Fraunces"', '"Inter"', 'serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      fontSize: {
        // Slightly tighter type scale than Tailwind defaults
        'xs':   ['0.75rem',  { lineHeight: '1.1rem' }],
        'sm':   ['0.875rem', { lineHeight: '1.3rem' }],
        'base': ['1rem',     { lineHeight: '1.6rem' }],
        'lg':   ['1.125rem', { lineHeight: '1.75rem' }],
        'xl':   ['1.25rem',  { lineHeight: '1.85rem' }],
        '2xl':  ['1.5rem',   { lineHeight: '2.1rem' }],
        '3xl':  ['1.875rem', { lineHeight: '2.4rem' }],
        '4xl':  ['2.25rem',  { lineHeight: '2.6rem' }],
        '5xl':  ['3rem',     { lineHeight: '1.1' }],
        '6xl':  ['3.75rem',  { lineHeight: '1.05' }],
        '7xl':  ['4.5rem',   { lineHeight: '1' }],
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '88': '22rem',
        '104': '26rem',
        '128': '32rem',
      },
      borderRadius: {
        'sm':  '0.25rem',
        DEFAULT: '0.375rem',
        'md':  '0.5rem',
        'lg':  '0.75rem',
        'xl':  '1rem',
        '2xl': '1.5rem',
      },
      boxShadow: {
        'soft':   '0 1px 2px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.06)',
        'card':   '0 2px 6px rgba(15,23,42,0.05), 0 6px 18px rgba(15,23,42,0.06)',
        'pop':    '0 8px 24px rgba(15,23,42,0.10), 0 2px 6px rgba(15,23,42,0.06)',
        'focus':  '0 0 0 3px rgba(59,115,230,0.35)',
      },
      ringColor: {
        DEFAULT: '#3b73e6',
      },
      transitionDuration: {
        '250': '250ms',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
