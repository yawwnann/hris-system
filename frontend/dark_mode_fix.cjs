const fs = require('fs');
const path = require('path');

const replacements = {
  'border-gray-200(?! dark:)': 'border-gray-200 dark:border-zinc-800',
  'border-gray-100(?! dark:)': 'border-gray-100 dark:border-zinc-800',
  'text-gray-500(?! dark:)': 'text-gray-500 dark:text-zinc-400',
  'text-gray-400(?! dark:)': 'text-gray-400 dark:text-zinc-500',
  'text-gray-600(?! dark:)': 'text-gray-600 dark:text-zinc-300',
  'text-gray-800(?! dark:)': 'text-gray-800 dark:text-zinc-200',
  'text-gray-900(?! dark:)': 'text-gray-900 dark:text-zinc-100',
  'bg-gray-50(?! dark:)': 'bg-gray-50 dark:bg-zinc-900/50',
  'bg-white(?! dark:)': 'bg-white dark:bg-zinc-950',
  'bg-gray-100(?! dark:)': 'bg-gray-100 dark:bg-zinc-800',
};

function processDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            processDir(fullPath);
        } else if (fullPath.endsWith('.vue') || fullPath.endsWith('.ts')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            let newContent = content;
            
            for (const [regexStr, replaceStr] of Object.entries(replacements)) {
                const regex = new RegExp(regexStr, 'g');
                newContent = newContent.replace(regex, replaceStr);
            }
            
            if (content !== newContent) {
                fs.writeFileSync(fullPath, newContent);
                console.log('Updated: ' + fullPath);
            }
        }
    }
}
processDir('/home/yawwnan/Project/HRIS SYSTEM/frontend/src/components/dashboard');
processDir('/home/yawwnan/Project/HRIS SYSTEM/frontend/src/components/layout');
processDir('/home/yawwnan/Project/HRIS SYSTEM/frontend/src/views');
