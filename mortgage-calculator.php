<?php
/**
 * iProply — Mortgage payment estimator (principal, interest, escrow-style add-ons).
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$currentPage     = 'mortgage-calculator';
$pageTitle       = 'Mortgage Calculator';
$metaTitle       = 'Mortgage Payment Calculator | ' . APP_NAME;
$metaDescription = 'Estimate your monthly mortgage payment: principal and interest, plus optional property tax, homeowners insurance, and HOA. For planning only — not a loan offer.';
$extraCss        = ['mortgage-calculator.css'];

$db           = Database::getInstance();
$siteSettings = $db->selectOne('site_settings', '*');

include __DIR__ . '/partials/header.php';
?>

<section class="mc-hero page-offset">
    <div class="container">
        <h1>Mortgage payment calculator</h1>
        <p>
            Plug in a few numbers to see an estimated monthly payment for principal &amp; interest, with optional taxes, insurance, and HOA.
            Rates and fees vary by lender — use this as a conversation starter, not a binding quote.
        </p>
    </div>
</section>

<section class="mc-wrap">
    <div class="container">
        <div class="mc-layout">
            <div class="mc-panel">
                <h2>Your numbers</h2>
                <div id="mc-error" class="mc-error" role="alert"></div>

                <div class="mc-field">
                    <label for="mc-price">Home price</label>
                    <input type="number" id="mc-price" min="0" step="1000" value="425000" inputmode="decimal" autocomplete="off">
                </div>

                <div class="mc-row">
                    <div class="mc-field">
                        <label for="mc-down-pct">Down payment (%)</label>
                        <input type="number" id="mc-down-pct" min="0" max="100" step="0.5" value="20" inputmode="decimal">
                        <div class="mc-field-hint" id="mc-down-amt-hint"></div>
                    </div>
                    <div class="mc-field">
                        <label for="mc-rate">Annual interest rate (%)</label>
                        <input type="number" id="mc-rate" min="0" max="25" step="0.125" value="6.5" inputmode="decimal">
                    </div>
                </div>

                <div class="mc-field">
                    <label for="mc-term">Loan term</label>
                    <select id="mc-term">
                        <option value="15">15 years</option>
                        <option value="20">20 years</option>
                        <option value="30" selected>30 years</option>
                    </select>
                </div>

                <p class="mc-pill">Optional monthly costs</p>

                <div class="mc-row">
                    <div class="mc-field">
                        <label for="mc-tax-year">Property tax (per year)</label>
                        <input type="number" id="mc-tax-year" min="0" step="100" value="4800" inputmode="decimal">
                    </div>
                    <div class="mc-field">
                        <label for="mc-ins-year">Home insurance (per year)</label>
                        <input type="number" id="mc-ins-year" min="0" step="100" value="1800" inputmode="decimal">
                    </div>
                </div>

                <div class="mc-field">
                    <label for="mc-hoa">HOA dues (per month)</label>
                    <input type="number" id="mc-hoa" min="0" step="25" value="0" inputmode="decimal">
                    <div class="mc-field-hint">Use 0 if none.</div>
                </div>

                <div class="mc-actions">
                    <button type="button" class="btn btn-primary" id="mc-calc"><i class="fas fa-calculator"></i> Update estimate</button>
                    <a class="btn btn-outline" href="<?php echo base_url('listings.php'); ?>" style="color:var(--primary);border-color:var(--primary)">
                        <i class="fas fa-search"></i> Browse homes
                    </a>
                </div>
            </div>

            <div class="mc-panel">
                <h2>Estimated payment</h2>
                <div class="mc-pill">Monthly</div>
                <p class="mc-results-total" id="mc-total" aria-live="polite"><span>$0</span></p>

                <ul class="mc-breakdown" id="mc-breakdown">
                    <li><span>Principal &amp; interest</span><strong id="mc-pi">—</strong></li>
                    <li><span>Property tax</span><strong id="mc-tax-mo">—</strong></li>
                    <li><span>Home insurance</span><strong id="mc-ins-mo">—</strong></li>
                    <li><span>HOA</span><strong id="mc-hoa-mo">—</strong></li>
                    <li><span>Loan amount</span><strong id="mc-loan">—</strong></li>
                </ul>

                <ul class="mc-breakdown" style="margin-top:1rem;padding-top:1rem;border-top:1px dashed var(--warm-200)">
                    <li><span>Total interest (over loan life)</span><strong id="mc-interest-total">—</strong></li>
                </ul>
            </div>
        </div>

        <p class="mc-disclaimer">
            <strong>Disclaimer:</strong> This calculator is provided by <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> for educational purposes only.
            It does not include mortgage insurance (PMI/MIP), lender fees, points, or escrow timing. Actual payments depend on your lender, credit profile, product, and underwriting.
            Equal Housing Opportunity.
        </p>
    </div>
</section>

<script>
(function () {
    'use strict';

    function money(n) {
        if (!isFinite(n) || n < 0) return '—';
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(Math.round(n));
    }

    function moneyCents(n) {
        if (!isFinite(n)) return '—';
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
    }

    function parseNum(id) {
        var el = document.getElementById(id);
        var v = parseFloat(String(el && el.value).replace(/,/g, ''));
        return isFinite(v) ? v : NaN;
    }

    function monthlyPI(principal, annualPct, years) {
        var n = Math.round(years * 12);
        if (n <= 0 || principal <= 0) return 0;
        var r = annualPct / 100 / 12;
        if (r === 0) return principal / n;
        return principal * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    }

    var errEl = document.getElementById('mc-error');

    function showErr(msg) {
        if (!msg) {
            errEl.textContent = '';
            errEl.classList.remove('is-visible');
            return;
        }
        errEl.textContent = msg;
        errEl.classList.add('is-visible');
    }

    function calc() {
        showErr('');
        var price = parseNum('mc-price');
        var downPct = parseNum('mc-down-pct');
        var rate = parseNum('mc-rate');
        var term = parseInt(document.getElementById('mc-term').value, 10);
        var taxY = parseNum('mc-tax-year');
        var insY = parseNum('mc-ins-year');
        var hoa = parseNum('mc-hoa');

        if (!isFinite(price) || price <= 0) { showErr('Enter a valid home price.'); return; }
        if (!isFinite(downPct) || downPct < 0 || downPct > 100) { showErr('Down payment percent should be between 0 and 100.'); return; }
        if (!isFinite(rate) || rate < 0) { showErr('Enter a valid interest rate.'); return; }
        if (!isFinite(term) || term <= 0) { showErr('Select a loan term.'); return; }

        var downAmt = price * (downPct / 100);
        var loan = price - downAmt;
        document.getElementById('mc-down-amt-hint').textContent =
            'Down payment ≈ ' + money(downAmt) + ' · Loan amount ' + money(loan);

        if (loan <= 0) { showErr('Down payment meets or exceeds the home price.'); return; }

        taxY = isFinite(taxY) && taxY >= 0 ? taxY : 0;
        insY = isFinite(insY) && insY >= 0 ? insY : 0;
        hoa = isFinite(hoa) && hoa >= 0 ? hoa : 0;

        var pi = monthlyPI(loan, rate, term);
        var taxMo = taxY / 12;
        var insMo = insY / 12;
        var n = Math.round(term * 12);
        var totalInterest = pi * n - loan;

        document.getElementById('mc-total').innerHTML = '<span>' + money(pi + taxMo + insMo + hoa) + '</span>/mo';
        document.getElementById('mc-pi').textContent = money(pi);
        document.getElementById('mc-tax-mo').textContent = money(taxMo);
        document.getElementById('mc-ins-mo').textContent = money(insMo);
        document.getElementById('mc-hoa-mo').textContent = money(hoa);
        document.getElementById('mc-loan').textContent = money(loan);
        document.getElementById('mc-interest-total').textContent =
            totalInterest > 0 ? moneyCents(totalInterest) : money(0);
    }

    document.getElementById('mc-calc').addEventListener('click', calc);
    ['mc-price','mc-down-pct','mc-rate','mc-term','mc-tax-year','mc-ins-year','mc-hoa'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { try { calc(); } catch (e) {} });
        if (el) el.addEventListener('change', function () { try { calc(); } catch (e) {} });
    });

    calc();
})();
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
