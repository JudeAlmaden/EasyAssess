import JavaScriptObfuscator from 'javascript-obfuscator';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Configuration for obfuscation
const obfuscationOptions = {
    compact: true,
    controlFlowFlattening: true,
    controlFlowFlatteningThreshold: 0.75,
    deadCodeInjection: true,
    deadCodeInjectionThreshold: 0.4,
    debugProtection: false,
    debugProtectionInterval: 0,
    disableConsoleOutput: false,
    identifierNamesGenerator: 'hexadecimal',
    log: false,
    numbersToExpressions: true,
    renameGlobals: false,
    selfDefending: true,
    simplify: true,
    splitStrings: true,
    splitStringsChunkLength: 10,
    stringArray: true,
    stringArrayCallsTransform: true,
    stringArrayEncoding: ['base64'],
    stringArrayIndexShift: true,
    stringArrayRotate: true,
    stringArrayShuffle: true,
    stringArrayWrappersCount: 2,
    stringArrayWrappersChainedCalls: true,
    stringArrayWrappersParametersMaxCount: 4,
    stringArrayWrappersType: 'function',
    stringArrayThreshold: 0.75,
    transformObjectKeys: true,
    unicodeEscapeSequence: false
};

// Files to obfuscate
const filesToObfuscate = [
    {
        input: 'resources/js/omr_create_scripts.js',
        output: 'public/js/obfuscated/omr_create_scripts.js'
    }
];

// Obfuscate each file
filesToObfuscate.forEach(file => {
    const inputPath = path.join(__dirname, file.input);
    const outputPath = path.join(__dirname, file.output);

    // Check if input file exists
    if (!fs.existsSync(inputPath)) {
        console.log(`⚠️  Skipping ${file.input} - file not found`);
        return;
    }

    console.log(`🔒 Obfuscating ${file.input}...`);

    try {
        const code = fs.readFileSync(inputPath, 'utf8');
        const obfuscationResult = JavaScriptObfuscator.obfuscate(code, obfuscationOptions);

        // Ensure output directory exists
        const outputDir = path.dirname(outputPath);
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        fs.writeFileSync(outputPath, obfuscationResult.getObfuscatedCode(), 'utf8');
        console.log(`✅ Successfully obfuscated to ${file.output}`);
    } catch (error) {
        console.error(`❌ Error obfuscating ${file.input}:`, error.message);
    }
});

console.log('\n🎉 Obfuscation complete!');
