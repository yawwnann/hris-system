const fs = require('fs');
const path = require('path');

const replacements = {
  'bg-green-50(?! dark:)': 'bg-green-50 dark:bg-green-900/20',
  'bg-green-100(?! dark:)': 'bg-green-100 dark:bg-green-900/40',
  'border-green-100(?! dark:)': 'border-green-100 dark:border-green-900/30',
  'text-green-600(?! dark:)': 'text-green-600 dark:text-green-400',
  'text-green-700(?! dark:)': 'text-green-700 dark:text-green-400',
  'text-green-800(?! dark:)': 'text-green-800 dark:text-green-400',
  
  'bg-orange-50(?! dark:)': 'bg-orange-50 dark:bg-orange-900/20',
  'bg-orange-100(?! dark:)': 'bg-orange-100 dark:bg-orange-900/40',
  'border-orange-100(?! dark:)': 'border-orange-100 dark:border-orange-900/30',
  'text-orange-700(?! dark:)': 'text-orange-700 dark:text-orange-400',
  'text-orange-800(?! dark:)': 'text-orange-800 dark:text-orange-400',
  
  'bg-red-50(?! dark:)': 'bg-red-50 dark:bg-red-900/20',
  'bg-red-100(?! dark:)': 'bg-red-100 dark:bg-red-900/40',
  'border-red-100(?! dark:)': 'border-red-100 dark:border-red-900/30',
  'text-red-600(?! dark:)': 'text-red-600 dark:text-red-400',
  'text-red-700(?! dark:)': 'text-red-700 dark:text-red-400',
  'text-red-800(?! dark:)': 'text-red-800 dark:text-red-400',
  
  'bg-blue-50(?! dark:)': 'bg-blue-50 dark:bg-blue-900/20',
  'bg-blue-100(?! dark:)': 'bg-blue-100 dark:bg-blue-900/40',
  'text-blue-700(?! dark:)': 'text-blue-700 dark:text-blue-400',
  
  'bg-yellow-50(?! dark:)': 'bg-yellow-50 dark:bg-yellow-900/20',
  'bg-yellow-100(?! dark:)': 'bg-yellow-100 dark:bg-yellow-900/40',
  'text-yellow-700(?! dark:)': 'text-yellow-700 dark:text-yellow-400',
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
