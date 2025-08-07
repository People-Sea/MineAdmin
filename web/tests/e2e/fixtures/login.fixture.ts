export const loginFixture = {
  validUser: {
    username: 'admin',
    password: '123456',
  },
  invalidUser: {
    username: 'admin',
    password: '12345',
  },
  emptyCredentials: {
    username: '',
    password: '',
  },
}
