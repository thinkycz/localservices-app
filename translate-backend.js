import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const csJsonPath = path.join(__dirname, 'lang', 'cs.json');
const enJsonPath = path.join(__dirname, 'lang', 'en.json');

const csData = JSON.parse(fs.readFileSync(csJsonPath, 'utf8'));
const enData = JSON.parse(fs.readFileSync(enJsonPath, 'utf8'));

const backendStrings = {
    "No service provider available for this service.": "Pro tuto službu není k dispozici žádný poskytovatel.",
    "Booking created successfully!": "Rezervace byla úspěšně vytvořena!",
    "This booking cannot be cancelled.": "Tuto rezervaci nelze zrušit.",
    "Booking cancelled successfully.": "Rezervace byla úspěšně zrušena.",
    "Thank you for your message! We will get back to you within 24 hours.": "Děkujeme za vaši zprávu! Ozveme se vám do 24 hodin.",
    "Booking confirmed successfully.": "Rezervace byla úspěšně potvrzena.",
    "Booking marked as completed.": "Rezervace byla označena jako dokončená.",
    "Notes added successfully.": "Poznámky byly úspěšně přidány.",
    "Welcome! Your service has been created successfully. You can now start receiving bookings.": "Vítejte! Vaše služba byla úspěšně vytvořena. Nyní můžete začít přijímat rezervace.",
    "Service created successfully. Now add your service offerings.": "Služba byla úspěšně vytvořena. Nyní přidejte své ceníky služeb.",
    "Service updated successfully.": "Služba byla úspěšně aktualizována.",
    "Service deleted successfully.": "Služba byla úspěšně smazána.",
    "Service offering added successfully.": "Položka služby byla úspěšně přidána.",
    "Service offering updated successfully.": "Položka služby byla úspěšně aktualizována.",
    "Service offering deleted successfully.": "Položka služby byla úspěšně smazána.",
    "Business hours updated successfully.": "Otevírací doba byla úspěšně aktualizována.",
    "You have already reviewed this booking.": "Tuto rezervaci jste již ohodnotili.",
    "Thank you for your review!": "Děkujeme za vaši recenzi!",
    "Service is now active.": "Služba je nyní aktivní.",
    "Service is now inactive.": "Služba je nyní neaktivní."
};

for (const [en, cs] of Object.entries(backendStrings)) {
    enData[en] = en;
    csData[en] = cs;
}

fs.writeFileSync(csJsonPath, JSON.stringify(csData, null, 4), 'utf8');
fs.writeFileSync(enJsonPath, JSON.stringify(enData, null, 4), 'utf8');

const targetFiles = [
    'app/Http/Controllers/BookingController.php',
    'app/Http/Controllers/Vendor/BookingController.php',
    'app/Http/Controllers/Vendor/OnboardingController.php',
    'app/Http/Controllers/PageController.php',
    'app/Http/Controllers/Vendor/ServicesController.php',
    'app/Http/Controllers/ReviewController.php'
];

for (const relPath of targetFiles) {
    const fullPath = path.join(__dirname, relPath);
    let content = fs.readFileSync(fullPath, 'utf8');

    // Replace basic static string matches
    content = content.replace(/->with\('(success|error)',\s*'([^']+)'\)/g, (match, type, text) => {
        return `->with('${type}', __('${text.replace(/'/g, "\\'")}'))`;
    });

    // Replace the dynamic one: "Service is now {$status}."
    content = content.replace(/->with\('success',\s*"Service is now \{\$status\}\."\)/g, `->with('success', $status === 'active' ? __('Service is now active.') : __('Service is now inactive.'))`);

    fs.writeFileSync(fullPath, content, 'utf8');
    console.log(`Updated ${fullPath}`);
}
