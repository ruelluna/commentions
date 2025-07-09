/** @type {import('stylelint').Config} */
export default {
  "extends": ["stylelint-config-standard", "stylelint-config-recommended"],
  "overrides": [
    {
      "files": ["resources/views/**/*.blade.php", "resources/**/*.js"],
      "customSyntax": "postcss-html"
    }
  ],
  rules: {
    "selector-class-pattern": [
      "^comm:.+",
      {
        "resolveNestedSelectors": true,
        "message": "Class selectors must have the 'comm:' prefix (e.g. 'comm:flex')"
      }
    ]
  }
}
