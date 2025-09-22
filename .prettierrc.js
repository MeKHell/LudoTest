import 'prettier-plugin-tailwindcss'

/**
 * @see https://prettier.io/docs/en/configuration.html
 * @type {import('prettier').Config}
 */
const config = {
    plugins: [
        /**
         * npm i -D prettier-plugin-tailwindcss
         */
        'prettier-plugin-tailwindcss',
    ],
    trailingComma: 'es5',
    tabWidth: 2,
    semi: false,
    singleQuote: true,
    wrapAttributes: 'aligned-multiple',
    singleAttributePerLine: false,
    printWidth: 100,
    sortTailwindcssClasses: false,
    tailwindcssConfigPath: './tailwind.config.js',
    sortHtmlAttributes: 'code-guide',
    tailwindPreserveDuplicates: false,
    tailwindFunctions: ['clsx', 'twMerge', 'classNames'],
}

export default config
