/**
 * careers.service.js
 */
// TODO: Add CAREERS_DATA to src/data/
export const careersService = {
  getOpenings: ({ department } = {}) => Promise.resolve([]),
  getById: (id) => Promise.reject(new Error(Job not found: )),
}
