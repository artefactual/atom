const { test, expect } = require('@playwright/test')

const privateFiles = [
  '/composer.json',
  '/composer.lock',
  '/src/README.md',
  '/config/package.xml',
  '/data/sql/lib.model.schema.sql',
  '/data/xslt/export-postprocess.xsl',
  '/README.md',
  '/SECURITY.md',
  '/phpunit.xml',
  '/vendor/saxon-he-10.6.jar',
]

test('[surface:workflow-private-file-boundary] rejects private project files', async ({
  request,
}) => {
  for (const path of privateFiles) {
    const response = await request.get(path)

    expect(response.status(), path).toBe(404)
  }
})
