<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Payslip Generator — Blue & White</title>
  <style>
    :root{
      --blue:#002147;
      --blue-dark:#002147;
      --muted:#6b7280;
      --card-shadow: 0 6px 18px rgba(11,121,208,0.08);
      --glass: rgba(255,255,255,0.9);
    }
    html,body{height:100%;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;background:linear-gradient(180deg,#f7fbff 0%, #ffffff 100%);color:#0f1724}
    .wrap{max-width:1100px;margin:28px auto;padding:24px}
    header{display:flex;align-items:center;gap:16px}
    .brand{display:flex;flex-direction:column}
    .brand h1{margin:0;font-size:20px;color:var(--blue)}
    .brand p{margin:0;color:var(--muted);font-size:13px}

    .container{display:grid;grid-template-columns:1fr 420px;gap:22px;margin-top:18px}
    .card{background:var(--glass);border-radius:12px;padding:18px;box-shadow:var(--card-shadow);}

    form .row{display:flex;gap:10px;margin-bottom:12px}
    label{font-size:13px;color:var(--muted);display:block;margin-bottom:6px}
    input[type=text], input[type=number], select{width:100%;padding:10px;border-radius:8px;border:1px solid #e6eef9;font-size:14px}

    .section-title{font-weight:600;color:var(--blue);margin:10px 0}
    .col-2{display:grid;grid-template-columns:1fr 1fr;gap:10px}

    .actions{display:flex;gap:10px;justify-content:flex-end;margin-top:12px}
    button{background:var(--blue);color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;font-weight:600}
    button.secondary{background:#e6f0fb;color:var(--blue);border:1px solid var(--blue)}
    button.ghost{background:transparent;color:var(--blue);border:1px dashed var(--blue)}

    /* Payslip preview */
    .payslip{padding:18px;border-radius:10px;border:1px solid #eaf5ff;background:linear-gradient(180deg,#ffffff,#fbfeff)}
    .payslip h2{margin:0 0 6px 0;color:var(--blue)}
    .meta{display:flex;justify-content:space-between;gap:10px;margin-bottom:8px}
    .table{width:100%;border-collapse:collapse;margin-top:8px}
    .table td{padding:8px;border-bottom:1px solid #eef6ff}
    .table .label{color:var(--muted)}
    .total{font-weight:700;color:var(--blue);font-size:18px}

    footer{margin-top:18px;text-align:center;color:var(--muted);font-size:13px}

    @media (max-width:980px){
      .container{grid-template-columns:1fr}
    }

    /* Print styles: print only the payslip area */
    @media print{
      body>*:not(.wrap){display:none}
      .wrap{margin:0;padding:0}
      .container{grid-template-columns:1fr}
      .form-area, .controls{display:none}
      .payslip{box-shadow:none;border:none}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="22" height="22" rx="6" fill="var(--blue)"/><path d="M7 11h10M7 15h6" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <div class="brand">
        <h1>Payslip Generator</h1>
        <p>Fill employee details, set deductions & allowances — generate & print payslip (client-side)</p>
      </div>
    </header>

    <div class="container">
      <div class="card form-area">
        <form id="payForm" onsubmit="event.preventDefault(); generatePayslip();">
          <div class="row">
            <div style="flex:1">
              <label>Employee Name</label>
              <input id="empName" type="text" placeholder="e.g. Rajesh Kumar" required />
            </div>
            <div style="width:150px">
              <label>Employee ID</label>
              <input id="empId" type="text" placeholder="E-001" />
            </div>
          </div>

          <div class="row">
            <div style="flex:1">
              <label>Designation</label>
              <input id="designation" type="text" placeholder="e.g. Software Engineer" />
            </div>
            <div style="width:160px">
              <label>Pay Date</label>
              <input id="payDate" type="text" placeholder="e.g. Oct 2025" />
            </div>
          </div>

          <div class="section-title">Earnings</div>
          <div class="col-2">
            <div>
              <label>Basic Salary (₹)</label>
              <input id="basic" type="number" min="0" step="0.01" value="30000" required />
            </div>
            <div>
              <label>HRA (₹)</label>
              <input id="hra" type="number" min="0" step="0.01" value="6000" />
            </div>
            <div>
              <label>Allowances (₹)</label>
              <input id="allow" type="number" min="0" step="0.01" value="2000" />
            </div>
            <div>
              <label>Bonus (₹)</label>
              <input id="bonus" type="number" min="0" step="0.01" value="0" />
            </div>
          </div>

          <div class="section-title">Deductions</div>
          <div class="col-2">
            <div>
              <label>Tax (₹)</label>
              <input id="tax" type="number" min="0" step="0.01" value="2500" />
            </div>
            <div>
              <label>Provident Fund (PF) (₹)</label>
              <input id="pf" type="number" min="0" step="0.01" value="1800" />
            </div>
            <div>
              <label>Other Deductions (₹)</label>
              <input id="otherDed" type="number" min="0" step="0.01" value="0" />
            </div>
            <div>
              <label>Late / Leave Deductions (₹)</label>
              <input id="late" type="number" min="0" step="0.01" value="0" />
            </div>
          </div>

          <div class="actions">
            <button type="button" class="secondary" onclick="fillExample()">Fill Example</button>
            <button type="button" class="ghost" onclick="resetForm()">Reset</button>
            <button type="submit">Generate Payslip</button>
          </div>
        </form>
      </div>

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
          <div>
            <div style="font-size:13px;color:var(--muted)">Preview</div>
            <strong style="color:var(--blue)">Payslip</strong>
          </div>
          <div class="controls">
            <button onclick="printPayslip()" style="margin-right:8px">Print / Save PDF</button>
            <button class="secondary" onclick="downloadJson()">Download JSON</button>
          </div>
        </div>

        <div id="preview" class="payslip">
          <div style="text-align:center;margin-bottom:8px">
            <h2>Company Name Pvt. Ltd.</h2>
            <div style="font-size:12px;color:var(--muted)">Payslip for the period — <span id="pvPeriod">-</span></div>
          </div>

          <div class="meta">
            <div>
              <div style="font-size:13px;font-weight:600" id="pvName">-</div>
              <div style="font-size:12px;color:var(--muted)" id="pvDesig">-</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:12px;color:var(--muted)">Emp ID: <span id="pvId">-</span></div>
              <div style="font-size:12px;color:var(--muted)">Pay Date: <span id="pvDate">-</span></div>
            </div>
          </div>

          <table class="table">
            <tr><td class="label">Basic Salary</td><td style="text-align:right" id="pvBasic">₹ 0.00</td></tr>
            <tr><td class="label">HRA</td><td style="text-align:right" id="pvHra">₹ 0.00</td></tr>
            <tr><td class="label">Allowances</td><td style="text-align:right" id="pvAllow">₹ 0.00</td></tr>
            <tr><td class="label">Bonus</td><td style="text-align:right" id="pvBonus">₹ 0.00</td></tr>
            <tr><td class="label">Gross Earnings</td><td style="text-align:right" id="pvGross">₹ 0.00</td></tr>

            <tr><td class="label">Tax</td><td style="text-align:right" id="pvTax">₹ 0.00</td></tr>
            <tr><td class="label">Provident Fund</td><td style="text-align:right" id="pvPf">₹ 0.00</td></tr>
            <tr><td class="label">Other Deductions</td><td style="text-align:right" id="pvOther">₹ 0.00</td></tr>
            <tr><td class="label">Late / Leave</td><td style="text-align:right" id="pvLate">₹ 0.00</td></tr>

            <tr><td class="label total">Net Pay</td><td style="text-align:right" class="total" id="pvNet">₹ 0.00</td></tr>
          </table>

        </div>
      </div>
    </div>

    <footer>Client-side payslip generator • No data leaves your browser • Use Print → Save as PDF to export</footer>
  </div>

  <script>
    function fmt(n){return '₹ ' + Number(n||0).toLocaleString('en-IN',{maximumFractionDigits:2,minimumFractionDigits:2})}

    function generatePayslip(){
      const empName = document.getElementById('empName').value.trim() || '-'
      const empId = document.getElementById('empId').value.trim() || '-'
      const desig = document.getElementById('designation').value.trim() || '-'
      const payDate = document.getElementById('payDate').value.trim() || new Date().toLocaleDateString()

      const basic = parseFloat(document.getElementById('basic').value) || 0
      const hra = parseFloat(document.getElementById('hra').value) || 0
      const allow = parseFloat(document.getElementById('allow').value) || 0
      const bonus = parseFloat(document.getElementById('bonus').value) || 0

      const tax = parseFloat(document.getElementById('tax').value) || 0
      const pf = parseFloat(document.getElementById('pf').value) || 0
      const otherDed = parseFloat(document.getElementById('otherDed').value) || 0
      const late = parseFloat(document.getElementById('late').value) || 0

      const gross = basic + hra + allow + bonus
      const totalDed = tax + pf + otherDed + late
      const net = gross - totalDed

      // Populate preview
      document.getElementById('pvName').textContent = empName
      document.getElementById('pvId').textContent = empId
      document.getElementById('pvDesig').textContent = desig
      document.getElementById('pvDate').textContent = new Date().toLocaleDateString()
      document.getElementById('pvPeriod').textContent = payDate

      document.getElementById('pvBasic').textContent = fmt(basic)
      document.getElementById('pvHra').textContent = fmt(hra)
      document.getElementById('pvAllow').textContent = fmt(allow)
      document.getElementById('pvBonus').textContent = fmt(bonus)
      document.getElementById('pvGross').textContent = fmt(gross)

      document.getElementById('pvTax').textContent = fmt(tax)
      document.getElementById('pvPf').textContent = fmt(pf)
      document.getElementById('pvOther').textContent = fmt(otherDed)
      document.getElementById('pvLate').textContent = fmt(late)

      document.getElementById('pvNet').textContent = fmt(net)

      // Attach current payslip JSON to preview element for download
      const payslipData = {employee:{name:empName,id:empId,designation:desig},period:payDate,generated_on:new Date().toISOString(),earnings:{basic,hra,allow,bonus,gross},deductions:{tax,pf,otherDed,late,totalDed},net}
      document.getElementById('preview').dataset.payslip = JSON.stringify(payslipData)

      // Focus the preview area
      document.getElementById('preview').scrollIntoView({behavior:'smooth'})
    }

    function printPayslip(){
      // Make sure preview is up-to-date
      generatePayslip()
      window.print()
    }

    function downloadJson(){
      generatePayslip()
      const blob = new Blob([document.getElementById('preview').dataset.payslip || '{}'], {type:'application/json'})
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = (document.getElementById('empName').value || 'payslip') + '.json'
      document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url)
    }

    function resetForm(){
      document.getElementById('payForm').reset()
      // clear preview
      document.querySelectorAll('#preview [id^="pv"]').forEach(el=>el.textContent = (el.id === 'pvPeriod' ? '-' : (el.classList && el.classList.contains('total')? '₹ 0.00':'-')))
      document.getElementById('pvGross').textContent = '₹ 0.00'
      document.getElementById('pvNet').textContent = '₹ 0.00'
      delete document.getElementById('preview').dataset.payslip
    }

    function fillExample(){
      document.getElementById('empName').value = 'Rajesh Kumar'
      document.getElementById('empId').value = 'E-102'
      document.getElementById('designation').value = 'Developer'
      document.getElementById('payDate').value = 'October 2025'
      document.getElementById('basic').value = 45000
      document.getElementById('hra').value = 9000
      document.getElementById('allow').value = 3000
      document.getElementById('bonus').value = 2000
      document.getElementById('tax').value = 6000
      document.getElementById('pf').value = 5400
      document.getElementById('otherDed').value = 0
      document.getElementById('late').value = 0
      generatePayslip()
    }

    // Auto-generate on first load
    window.addEventListener('DOMContentLoaded', ()=>{
      document.getElementById('payDate').value = new Date().toLocaleString('en-GB',{month:'long',year:'numeric'})
      generatePayslip()
    })
  </script>
</body>
</html>