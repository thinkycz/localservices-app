import { existsSync, rmSync } from 'node:fs';

export default function globalTeardown() {
    if (process.env.DB_DATABASE && existsSync(process.env.DB_DATABASE)) {
        rmSync(process.env.DB_DATABASE);
    }
}
