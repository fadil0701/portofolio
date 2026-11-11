// Phase 0 foundation validator â€” comment-aware.
import { readFileSync } from 'node:fs';

const F = {
  tokens: 'resources/css/design-tokens.css',
  components: 'resources/css/components.css',
  app: 'resources/css/app.css',
  styleguide: 'docs/styleguide/index.html',
};

let fails = 0;
const check = (label, ok, detail = '') => {
  if (!ok) fails++;
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${label}${detail ? '  -- ' + detail : ''}`);
};

const stripCss = (src) => src.replace(/\/\*[\s\S]*?\*\//g, '');
const stripHtml = (src) => src.replace(/<!--[\s\S]*?-->/g, '');

const rawTokens = readFileSync(F.tokens, 'utf8');
const rawComp = readFileSync(F.components, 'utf8');
const rawApp = readFileSync(F.app, 'utf8');
const rawSg = readFileSync(F.styleguide, 'utf8');

const tokens = stripCss(rawTokens);
const comp = stripCss(rawComp);
const app = stripCss(rawApp);
const sg = stripHtml(rawSg);

// ---------- 1. Structural integrity ----------
for (const [name, src] of [['tokens', rawTokens], ['components', rawComp], ['app', rawApp]]) {
  const o = (src.match(/\{/g) || []).length, c = (src.match(/\}/g) || []).length;
  check(`${name}.css braces balanced`, o === c, `open=${o} close=${c}`);
  const po = (src.match(/\(/g) || []).length, pc = (src.match(/\)/g) || []).length;
  check(`${name}.css parens balanced`, po === pc, `open=${po} close=${pc}`);
  const co = (src.match(/\/\*/g) || []).length, cc = (src.match(/\*\//g) || []).length;
  check(`${name}.css comments terminated`, co === cc, `open=${co} close=${cc}`);
}

// Comment parsing must be UNAMBIGUOUS: if a "*/" appears inside comment prose
// (e.g. a glob like views/**/*.blade.php) the comment closes early and the
// prose below leaks into the stylesheet as CSS. Detect that class of bug:
//   1. the stripped source must contain no remaining comment markers
//   2. known prose sentinels must not survive stripping
const strip = (s) => s.replace(/\/\*[\s\S]*?\*\//g, '');
for (const [name, src] of [['app', rawApp], ['components', rawComp], ['tokens', rawTokens]]) {
  const stripped = strip(src);
  check(`${name}.css comment parsing unambiguous`, !stripped.includes('/*') && !stripped.includes('*/'));
}
check('app.css prose did not leak into CSS',
  !/INTEGRATION CONTRACT/.test(strip(rawApp)) && !/self-referential/.test(strip(rawApp)));
check('app.css retains @import statements', /@import\s+['"]tailwindcss['"]/.test(strip(rawApp)));

// ---------- 2. Tokens & the WCAG 1.4.11 contract ----------
const colorDecls = rawTokens.match(/--color-[a-z0-9-]+:\s*[^;]+;/g) || [];
const dual = colorDecls.filter((d) => d.includes('light-dark(')).length;
console.log(`INFO  colour tokens: ${colorDecls.length} total, ${dual} mode-aware, ${colorDecls.length - dual} deliberate single-mode`);

const ldCalls = tokens.match(/light-dark\(([^;]*?)\);/g) || [];
const badLd = ldCalls.filter((c) => {
  const inner = c.slice('light-dark('.length, -2);
  let depth = 0, commas = 0;
  for (const ch of inner) {
    if (ch === '(') depth++; else if (ch === ')') depth--;
    else if (ch === ',' && depth === 0) commas++;
  }
  return commas !== 1;
});
check('all light-dark() calls have exactly 2 args', badLd.length === 0, `bad=${badLd.length}`);
check('--color-border-strong defined (contrast-bearing)', /--color-border-strong\s*:/.test(tokens));
check('--color-focus-ring defined', /--color-focus-ring\s*:/.test(tokens));
check('--color-border-input aliases border-strong', /--color-border-input:\s*var\(--color-border-strong\)/.test(tokens));
const breakpointNames = ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];
const missingBreakpoints = breakpointNames.filter((name) =>
  !new RegExp(`--breakpoint-${name}:\\s*\\d+(?:\\.\\d+)?rem\\s*;`).test(tokens)
);
check('canonical breakpoints use literal Tailwind @theme values',
  /@theme\s*\{/.test(tokens) && missingBreakpoints.length === 0, `missing=[${missingBreakpoints.join(', ')}]`);
check('text-on-solid tokens declared for filled controls',
  /--color-text-on-solid:/.test(tokens) && /--color-text-on-secondary:/.test(tokens) && /--color-text-on-accent:/.test(tokens));

// ---------- 3. Component layer ----------
check('components.css uses @layer base', /@layer base\s*\{/.test(comp));
check('components.css uses @layer components', /@layer components\s*\{/.test(comp));
check('components.css closes @layer components', /end @layer components/.test(rawComp));

const required = ['os-container','os-section','os-stack','os-grid','os-divider','os-btn','os-icon-btn',
  'os-badge','os-input','os-textarea','os-select','os-check','os-toggle','os-modal','os-menu','os-tooltip',
  'os-tab','os-accordion','os-page-link','os-toast','os-alert','os-skeleton','os-empty-state','os-error-state',
  'os-card','os-navbar','os-mobile-nav','os-breadcrumb','os-footer','os-admin','os-sidebar','os-metric',
  'os-timeline','os-exp-item','os-table','os-toolbar','os-project-card','os-skill','os-expertise-card',
  'os-terminal','os-arch-node','os-chart-bar','os-dropzone','os-upload-item','os-media-tile','os-prose',
  'os-hero','os-chamfer','os-signal-rule','os-grid-overlay','os-grain','os-visually-hidden','os-skip-link'];
const missing = required.filter((c) => !comp.includes('.' + c));
check(`all ${required.length} documented primitives exist`, missing.length === 0, missing.join(', '));

const compHex = comp.match(/#[0-9a-fA-F]{3,8}\b/g) || [];
check('components.css has ZERO raw hex colours', compHex.length === 0, compHex.join(', '));
check('components.css has zero mode-specific overrides (tokens carry the mode)', !/\[data-theme=/.test(comp));

const definedKf = new Set((comp.match(/@keyframes\s+([a-z0-9-]+)/g) || []).map((s) => s.split(/\s+/)[1]));
const usedKf = new Set([...comp.matchAll(/animation:\s*([a-z0-9-]+)/g)].map((m) => m[1]));
const undefKf = [...usedKf].filter((k) => !definedKf.has(k));
check('every referenced keyframe is defined', undefKf.length === 0, `undef=[${undefKf.join(', ')}]`);

// ---------- 4. Tailwind integration ----------
check('app.css imports tailwindcss', /@import\s+['"]tailwindcss['"]/.test(app));
check('app.css imports design-tokens.css', /@import\s+['"]\.\/design-tokens\.css['"]/.test(app));
check('app.css imports components.css', /@import\s+['"]\.\/components\.css['"]/.test(app));
check('app.css clears colliding namespaces', /--color-\*:\s*initial/.test(app) && /--radius-\*:\s*initial/.test(app));
check('app.css does not alias breakpoints through CSS variables', !/--breakpoint-[a-z0-9-]+:\s*var\(/.test(app));
check('app.css defines data-theme dark variant', /@custom-variant dark/.test(app));
check('no circular token alias (--x: var(--x))', !/--([a-z0-9-]+):\s*var\(--\1\)/.test(app));

// ---------- 5. Style guide ----------
for (const tag of ['div','section','span','main','aside','table','tbody','thead','tr','pre','a','ul','li','p','button','nav','header','footer']) {
  const o = (sg.match(new RegExp(`<${tag}[\\s>]`, 'gi')) || []).length;
  const c = (sg.match(new RegExp(`</${tag}>`, 'gi')) || []).length;
  check(`styleguide <${tag}> balanced`, o === c, `open=${o} close=${c}`);
}
check('styleguide links design-tokens.css', sg.includes('../../resources/css/design-tokens.css'));
check('styleguide links components.css', sg.includes('../../resources/css/components.css'));
check('styleguide has skip link', sg.includes('os-skip-link'));
check('styleguide has theme toggle', sg.includes('id="theme-toggle"'));
const sections = (sg.match(/class="sg-section"/g) || []).length;
check('styleguide covers all sections', sections >= 15, `sections=${sections}`);
check('styleguide ids unique', (() => { const ids = [...sg.matchAll(/\sid="([^"]+)"/g)].map((m) => m[1]); return ids.length === new Set(ids).size; })());

// ---------- 6. WCAG contrast for every declared solid-fill + text pairing ----------
const hexToRgb = (h) => { const s = h.replace('#',''); const n = s.length===3? s.split('').map(c=>c+c).join('') : s; return [0,2,4].map(i=>parseInt(n.slice(i,i+2),16)); };
const lin = (c) => { const s=c/255; return s<=0.03928? s/12.92 : Math.pow((s+0.055)/1.055,2.4); };
const lum = (h) => { const [r,g,b]=hexToRgb(h).map(lin); return 0.2126*r+0.7152*g+0.0722*b; };
const ratio = (a,b) => { const [l1,l2]=[lum(a),lum(b)].sort((x,y)=>y-x); return (l1+0.05)/(l2+0.05); };

const resolve = (name, mode) => {
  const m = tokens.match(new RegExp(`${name}:\\s*light-dark\\(([^,]+),\\s*([^)]+)\\);`));
  if (m) return (mode === 'light' ? m[1] : m[2]).trim();
  const m2 = tokens.match(new RegExp(`${name}:\\s*(#[0-9a-fA-F]{3,8})\\s*;`));
  return m2 ? m2[1] : null;
};

// Each entry: [label, mode, fillToken, expectedTextColourToken]
const pairs = [
  ['primary',              'light', '--color-brand-primary',            '--color-text-on-solid'],
  ['primary',              'dark',  '--color-brand-primary',            '--color-text-on-solid'],
  ['primary hover',        'light', '--color-brand-primary-hover',      '--color-text-on-solid'],
  ['primary hover',        'dark',  '--color-brand-primary-hover',      '--color-text-on-solid'],
  ['secondary',            'light', '--color-brand-secondary-solid',    '--color-text-on-secondary'],
  ['secondary',            'dark',  '--color-brand-secondary-solid',    '--color-text-on-secondary'],
  ['secondary hover',      'light', '--color-brand-secondary-hover',    '--color-text-on-secondary'],
  ['secondary hover',      'dark',  '--color-brand-secondary-hover',    '--color-text-on-secondary'],
  ['danger',               'light', '--color-danger-solid',             '--color-text-on-solid'],
  ['danger',               'dark',  '--color-danger-solid',             '--color-text-on-solid'],
  ['danger hover',         'light', '--color-danger-solid-hover',       '--color-text-on-solid'],
  ['danger hover',         'dark',  '--color-danger-solid-hover',       '--color-text-on-solid'],
  ['accent',               'light', '--color-brand-accent-solid',       '--color-text-on-accent'],
  ['accent',               'dark',  '--color-brand-accent-solid',       '--color-text-on-accent'],
  ['accent hover',         'light', '--color-brand-accent-solid-hover', '--color-text-on-accent'],
  ['accent hover',         'dark',  '--color-brand-accent-solid-hover', '--color-text-on-accent'],
];

let cFail = 0;
for (const [label, mode, fillTok, textTok] of pairs) {
  const fill = resolve(fillTok, mode);
  const text = resolve(textTok, mode);
  if (!fill || !text) { check(`contrast ${label} (${mode})`, false, 'token unresolved'); cFail++; continue; }
  const r = ratio(text, fill);
  const ok = r >= 4.5;
  if (!ok) cFail++;
  check(`contrast ${label} ${mode}: ${fill} + ${text} = ${r.toFixed(2)}`, ok);
}
console.log(`INFO  solid-fill/text pairs clearing AA (4.5:1): ${pairs.length - cFail}/${pairs.length}`);

console.log(`\n${fails === 0 ? 'ALL CHECKS PASSED' : fails + ' CHECK(S) FAILED'}`);
process.exit(fails === 0 ? 0 : 1);
