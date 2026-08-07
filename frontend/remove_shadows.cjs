const fs = require('fs');
const path = require('path');

function processDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            processDir(fullPath);
        } else if (fullPath.endsWith('.vue') || fullPath.endsWith('.ts')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            // Remove shadow, shadow-sm, shadow-md, shadow-lg, shadow-none, hover:shadow-sm etc
            let newContent = content.replace(/\b(?:hover:)?shadow(?:-(?:sm|md|lg|none))?\b/g, '');
            // Clean up multiple spaces that might have been left
            newContent = newContent.replace(/  +/g, ' ');
            if (content !== newContent) {
                fs.writeFileSync(fullPath, newContent);
                console.log('Updated: ' + fullPath);
            }
        }
    }
}

processDir('/home/yawwnan/Project/HRIS SYSTEM/frontend/src');
