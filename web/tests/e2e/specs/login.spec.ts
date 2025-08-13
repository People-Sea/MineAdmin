import { expect, test } from '@playwright/test'
import { LoginPage } from '@pages/login'
import { loginFixture } from '@fixtures/login'

test.describe('Login', () => {
  let loginPage: LoginPage

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page)
    await loginPage.goto()
  })

  test('Empty Voucher', async () => {
    const { username, password } = loginFixture.emptyVoucher
    await loginPage.login(username, password)

    await expect(loginPage.page.locator('.m-message-wrapper .m-message--description:has-text("请输入密码")').first()).toBeVisible()
  })

  test('Invalid Voucher', async () => {
    const { username, password } = loginFixture.invalidUser
    await loginPage.login(username, password)
    await expect(loginPage.page.locator('.m-message-wrapper .m-message--description:has-text("密码错误")').first()).toBeVisible()
  })
})
