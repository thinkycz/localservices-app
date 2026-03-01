import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const csJsonPath = path.join(__dirname, 'lang', 'cs.json');
const enJsonPath = path.join(__dirname, 'lang', 'en.json');

const csData = JSON.parse(fs.readFileSync(csJsonPath, 'utf8'));
const enData = JSON.parse(fs.readFileSync(enJsonPath, 'utf8'));

const translations = {
    "Booking Confirmation": "Potvrzení rezervace",
    "Thank you for booking with us!": "Děkujeme za rezervaci!",
    "Your booking has been received and is currently": "Vaše rezervace byla přijata a aktuálně je",
    "Pending": "Čekající",
    ". We'll notify you once the vendor confirms your appointment.": ". Upozorníme vás, jakmile poskytovatel potvrdí vaši schůzku.",
    "Booking Details": "Detaily rezervace",
    "Service": "Služba",
    "Offering": "Balíček",
    "Date": "Datum",
    "Time": "Čas",
    "Duration": "Trvání",
    "Total Price": "Celková cena",
    "Vendor": "Poskytovatel",
    "Booking ID": "ID rezervace",
    "Customer Notes": "Poznámky zákazníka",
    "Your Notes": "Vaše poznámky",
    "You can view and manage your bookings anytime by visiting your account:": "Své rezervace můžete kdykoli spravovat ve svém účtu:",
    "If you have any questions, please don't hesitate to contact us.": "Pokud máte nějaké dotazy, neváhejte nás kontaktovat.",
    "Booking Status Update": "Aktualizace stavu rezervace",
    "Your booking status has changed": "Stav vaší rezervace se změnil",
    "Your booking is now:": "Vaše rezervace je nyní:",
    "What's next?": "Co bude dál?",
    "Please arrive 5-10 minutes before your scheduled time": "Dostavte se prosím 5-10 minut před plánovaným začátkem",
    "If you need to reschedule, please contact us at least 24 hours in advance": "Pokud potřebujete přesunout termín, kontaktujte nás prosím alespoň 24 hodin předem",
    "Bring any relevant documents or information related to your service": "Přineste si s sebou všechny potřebné dokumenty nebo informace související se službou",
    "We hope you enjoyed your experience!": "Doufáme, že se vám u nás líbilo!",
    "Leave a Review": "Napsat recenzi",
    "New Contact Submission": "Nový kontaktní formulář",
    "Type": "Typ",
    "From": "Od",
    "Subject": "Předmět",
    "Submitted": "Odesláno",
    "Message": "Zpráva",
    "New Booking Received": "Nová rezervace",
    "New Booking Received!": "Nová rezervace!",
    "You have a new booking request": "Máte novou žádost o rezervaci",
    "🎉 You have a new booking that requires your confirmation!": "🎉 Máte novou rezervaci, která vyžaduje vaše potvrzení!",
    "A customer has booked your service. Please review the details below and confirm or decline the booking.": "Zákazník si zarezervoval vaši službu. Zkontrolujte prosím níže uvedené podrobnosti a potvrďte nebo odmítněte rezervaci.",
    "Customer Information": "Informace o zákazníkovi",
    "Name": "Jméno",
    "Email": "E-mail",
    "Phone": "Telefon",
    "Status": "Status",
    "View & Manage Booking": "Zobrazit a spravovat rezervaci",
    "Quick Actions:": "Rychlé akce:",
    "• Confirm the booking to secure the appointment": "• Potvrďte rezervaci pro zajištění termínu",
    "• Contact the customer if you need more information": "• Kontaktujte zákazníka, pokud potřebujete více informací",
    "• Decline if you're not available": "• Odmítněte, pokud nemáte volno",
    "This is an automated notification from your LocalServices vendor dashboard.": "Toto je automatické upozornění z vašeho panelu poskytovatele LocalServices.",
    "Payment Successful": "Platba proběhla úspěšně",
    "Thanks for your payment": "Děkujeme za vaši platbu",
    "Your payment was received successfully.": "Vaše platba byla úspěšně přijata.",
    "Booking": "Rezervace",
    "Amount": "Částka",
    "Paid At": "Zaplaceno",
    "View Booking": "Zobrazit rezervaci"
};

// Cleanup garbage keys from blade extraction
const garbageKeys = [];
for (const key of Object.keys(csData)) {
    if (key.includes('}}') || key === '@inertia' || key.includes('->') || key.includes('@if') || key.includes('@endif') || key.includes('@php') || key.includes('}')) {
        garbageKeys.push(key);
    }
}

for (const k of garbageKeys) {
    delete csData[k];
    delete enData[k];
}

// Update translations
for (const [en, cs] of Object.entries(translations)) {
    if (csData[en]) {
        csData[en] = cs;
    }
}

// Any remaining (CS) should just be the english key or removed.
for (const [key, val] of Object.entries(csData)) {
    if (val.endsWith(' (CS)')) {
        csData[key] = key; // Fallback to english if missed
    }
}

fs.writeFileSync(csJsonPath, JSON.stringify(csData, null, 4), 'utf8');
fs.writeFileSync(enJsonPath, JSON.stringify(enData, null, 4), 'utf8');

console.log('Cleaned up translations!');
