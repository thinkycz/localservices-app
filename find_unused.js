const fs = require('fs');
const path = require('path');

function walk(dir, fn) {
    if (!fs.existsSync(dir)) return;
    const files = fs.readdirSync(dir);
    for (const f of files) {
        const p = path.join(dir, f);
        if (fs.statSync(p).isDirectory()) walk(p, fn);
        else fn(p);
    }
}

const vueFiles = [];
walk('resources/js', p => { if (p.endsWith('.vue')) vueFiles.push(p); });

let content = '';
const skipDirs = ['node_modules', 'vendor', '.git', 'storage', 'public'];
function walkAll(dir) {
    if (!fs.existsSync(dir)) return;
    const files = fs.readdirSync(dir);
    for (const f of files) {
        const p = path.join(dir, f);
        if (skipDirs.some(s => p.includes(s))) continue;
        if (fs.statSync(p).isDirectory()) walkAll(p);
        else if (p.endsWith('.php') || p.endsWith('.vue') || p.endsWith('.js')) {
            try { content += fs.readFileSync(p, 'utf8') + '\n'; } catch (e) { }
        }
    }
}
walkAll('.');

const unused = [];
for (const f of vueFiles) {
    if (f.includes('/Pages/')) {
        const p = f.split('/Pages/')[1].replace('.vue', '');
        // Exceptions for commonly imported pages like Home
        if (p === 'Home') continue;
        if (!content.includes(`'${p}'`) && !content.includes(`"${p}"`) && !content.includes(`render('${p}'`)) {
            unused.push(f);
        }
    } else {
        const name = path.basename(f, '.vue');
        // Count occurrences of the component name in the entire text
        const count = content.split(name).length - 1;
        // 1 occurrence usually means its own export statement or similar isolated definition
        if (count <= 1) unused.push(f);
    }
}

console.log(JSON.stringify(unused, null, 2));
