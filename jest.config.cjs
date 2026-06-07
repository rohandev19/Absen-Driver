// jest.config.cjs — Jest configuration for maintenance-component-interval-guide tests
// Using .cjs extension because package.json has "type": "module".
// Jest test files use CommonJS (require/module.exports).
// The transform:{} setting ensures Jest uses the files as-is without babel transform.
module.exports = {
  testEnvironment: 'jsdom',
  testMatch: ['**/tests/js/**/*.test.js'],
  transform: {},
};
