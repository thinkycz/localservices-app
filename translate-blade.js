import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const directoryPath = path.join(__dirname, 'resources/views');
const csJsonPath = path.join(__dirname, 'lang', 'cs.json');
const enJsonPath = path.join(__dirname, 'lang', 'en.json');

const csData = JSON.parse(fs.readFileSync(csJsonPath, 'utf8'));
const enData = JSON.parse(fs.readFileSync(enJsonPath, 'utf8'));

export function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');

    // Regex to match text inside HTML tags
    // >Text here<
    const textRegex = />([^<>{]+)</g;

    content = content.replace(textRegex, (match, text) => {
        let trimmedText = text.trim();

        // Skip empty, only numbers, or single chars
        if (!trimmedText || trimmedText.length <= 1 || !/[a-zA-Z]/.test(trimmedText)) {
            return match;
        }

        // Clean up formatting
        let key = trimmedText.replace(/\s+/g, ' ');

        // Don't override if already existing logic or translation exists
        if (!enData[key]) enData[key] = key;
        if (!csData[key]) csData[key] = key + " (CS)"; // Placeholder for new ones

        // Replace in template
        const replacement = text.replace(trimmedText, `{{ __('${key.replace(/'/g, "\\'")}') }}`);
        return `>${replacement}<`;
    });

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Processed Blade: ${filePath}`);
}

function walkDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            walkDir(fullPath);
        } else if (fullPath.endsWith('.blade.php')) {
            processFile(fullPath);
        }
    }
}

console.log('Starting extraction for Blade files...');
walkDir(directoryPath);

fs.writeFileSync(csJsonPath, JSON.stringify(csData, null, 4), 'utf8');
fs.writeFileSync(enJsonPath, JSON.stringify(enData, null, 4), 'utf8');

console.log('Done! Generated lang files for Blade templates.');
