import { expect, test } from '@playwright/test'
import { LoginPage } from '@pages/login'

test.describe('Base', () => {
  let loginPage: LoginPage

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page)
    await loginPage.goto()
  })

  test('Check Title', async () => {
    await expect(loginPage.page).toHaveTitle('MineAdmin')
  })

  test('Check Api Server', async () => {
    // The request is initiated from the login page and routed through the Vite proxy (http://localhost:9501/)
    const response = await loginPage.page.request.get('dev')
    expect(response.ok()).toBeTruthy()

    const content = await response.text()
    expect(content).toContain('welcome use mineAdmin')
  })
})
