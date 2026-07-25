const fs = require('fs')
const path = require('path')
const { surface } = require('./authenticated-surface')

class CoverageReporter {
  constructor() {
    this.passed = new Set()
  }

  onTestEnd(test, result) {
    if ('passed' !== result.status) {
      return
    }

    for (const match of test.title.matchAll(/\[surface:([^\]]+)\]/g)) {
      this.passed.add(match[1])
    }
  }

  onEnd() {
    const expected = surface.map((item) => item.id)
    const covered = expected.filter((id) => this.passed.has(id))
    const missing = expected.filter((id) => !this.passed.has(id))
    const percentage = Number(
      ((covered.length / expected.length) * 100).toFixed(2)
    )
    const minimum = Number(process.env.PLAYWRIGHT_COVERAGE_MINIMUM || 80)
    const report = {
      metric: 'authenticated UI surface',
      minimum,
      percentage,
      covered: covered.length,
      total: expected.length,
      missing,
    }
    const output = path.resolve('output/playwright')

    fs.mkdirSync(output, { recursive: true })
    fs.writeFileSync(
      path.join(output, 'coverage.json'),
      `${JSON.stringify(report, null, 2)}\n`
    )

    console.log(
      `\nAuthenticated UI surface coverage: ` +
        `${covered.length}/${expected.length} (${percentage}%)`
    )

    if (missing.length) {
      console.log(`Missing surface IDs: ${missing.join(', ')}`)
    }

    if (
      '1' === process.env.PLAYWRIGHT_COVERAGE &&
      percentage < minimum
    ) {
      console.error(
        `Coverage ${percentage}% is below the required ${minimum}%.`
      )
      process.exitCode = 1
    }
  }
}

module.exports = CoverageReporter
