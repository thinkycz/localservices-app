import { execFileSync, spawn } from 'node:child_process';
import { closeSync, existsSync, mkdirSync, openSync, rmSync } from 'node:fs';
import { dirname } from 'node:path';

const database = process.env.DB_DATABASE;

if (!database) {
    throw new Error('DB_DATABASE is required for the disposable E2E server.');
}

mkdirSync(dirname(database), { recursive: true });
if (existsSync(database)) rmSync(database);
closeSync(openSync(database, 'w'));

execFileSync('php', ['artisan', 'migrate:fresh', '--seed', '--force'], {
    env: process.env,
    stdio: 'inherit',
});

const server = spawn(
    'php',
    ['artisan', 'serve', '--host=127.0.0.1', '--port=8173', '--no-reload'],
    { env: process.env, stdio: 'inherit' },
);

for (const signal of ['SIGINT', 'SIGTERM']) {
    process.on(signal, () => server.kill(signal));
}

server.on('exit', (code) => process.exit(code ?? 0));
