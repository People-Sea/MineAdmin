import { expect, test } from '@playwright/test'
import { LoginPage } from '@pages/login'
import { loginFixture } from '@fixtures/login'

test.describe('login', () => {
  let loginPage: LoginPage

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page)
    await loginPage.goto()
  })

  test('空凭据登录失败', async () => {
    const { username, password } = loginFixture.emptyCredentials
    await loginPage.login(username, password)
    // console.log(loginPage.page.locator('.m-message').first())
    await expect(loginPage.page.locator('.m-message-wrapper .m-message--description:has-text("请输入密码")').first()).toBeVisible()

  })

  test('错误凭据登录失败', async () => {
    const { username, password } = loginFixture.invalidUser
    await loginPage.login(username, password)
    await expect(loginPage.page.locator('.m-message-wrapper .m-message--description:has-text("用户名或密码错误")').first()).toBeVisible()
  })
})
