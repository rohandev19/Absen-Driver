// jest.config.js — Jest configuration for maintenance-component-interval-guide tests
// Note: package.json has "type": "module", but Jest test files use CommonJS (require/module.exports)
// The transform setting ensures Jest can process CJS test files correctly.
module.exports = {
  testEnvironment: 'jsdom',
  testMatch: ['**/tests/js/**/*.test.js'],
  transform: {},
};
