import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const directoryPath = path.join(__dirname, 'resources/js');
const enJsonPath = path.join(__dirname, 'lang', 'en.json');
const csJsonPath = path.join(__dirname, 'lang', 'cs.json');

const enTranslations = {};
const csTranslations = {};

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');

    // Only process the <template> part to avoid messing up with scripts
    const templateMatch = content.match(/<template>([\s\S]*?)<\/template>/);
    if (!templateMatch) return;

    let templateContent = templateMatch[1];

    // Regex to match text inside HTML tags
    // >Text here<
    const textRegex = />([^<>{]+)</g;

    templateContent = templateContent.replace(textRegex, (match, text) => {
        let trimmedText = text.trim();

        // Skip empty, only numbers, or single chars
        if (!trimmedText || trimmedText.length <= 1 || !/[a-zA-Z]/.test(trimmedText)) {
            return match;
        }

        // Skip if it looks like code or variable binding
        if (trimmedText.includes('{{') || trimmedText.includes('}}') || trimmedText.includes('v-') || trimmedText.includes('$t(')) {
            return match;
        }

        // Clean up formatting
        let key = trimmedText.replace(/\s+/g, ' ');
        enTranslations[key] = key;

        // Replace in template
        const replacement = `>{{ $t('${key.replace(/'/g, "\\'")}') }}<`;
        return match.replace(`>${text}<`, replacement);
    });

    // Also look for simple props like placeholder="string" or label="string"
    const attrRegex = /\b(placeholder|title|label|alt)="([^"]+)"/g;
    templateContent = templateContent.replace(attrRegex, (match, attrName, attrValue) => {
        if (!attrValue.trim() || !/[a-zA-Z]/.test(attrValue) || attrValue.includes('{{')) return match;
        let key = attrValue.trim();
        enTranslations[key] = key;
        // Bind the attribute: :placeholder="$t('key')"
        return `:${attrName}="$t('${key.replace(/'/g, "\\'")}')"`;
    });

    content = content.replace(templateMatch[1], templateContent);
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Processed: ${filePath}`);
}

function walkDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            walkDir(fullPath);
        } else if (fullPath.endsWith('.vue')) {
            processFile(fullPath);
        }
    }
}

console.log('Starting extraction...');
walkDir(directoryPath);

// Create some default CS translations to populate cs.json
// Note: We'll fill this with DeepL/Google API or hardcoded map later, for now just empty or exact copy
for (const key in enTranslations) {
    csTranslations[key] = key + ' (CS)'; // Placeholder
}

fs.writeFileSync(enJsonPath, JSON.stringify(enTranslations, null, 4), 'utf8');
fs.writeFileSync(csJsonPath, JSON.stringify(csTranslations, null, 4), 'utf8');

console.log('Done! Generated lang/en.json and lang/cs.json');
