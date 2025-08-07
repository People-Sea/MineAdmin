import { expect, test } from '@playwright/test'
import { LoginPage } from '@pages/login'

test.describe('base', () => {
  let loginPage: LoginPage

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page)
    await loginPage.goto()
  })

  test('Check Title', async () => {
    await expect(loginPage.page).toHaveTitle('MineAdmin')
  })

  test('Check Api Server', async ({ page }) => {
    const response = await page.request.get('http://localhost:9501')
    expect(response.ok()).toBeTruthy()
    const body = await response.text()
    expect(body).toContain('welcome use mineAdmin')
  })
})
