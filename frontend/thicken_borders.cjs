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
            
            // Replace border-gray-100 on Cards specifically, or generally just replace border-gray-100 with border-gray-200
            // and add border-2 where we have border or border-gray-200
            let newContent = content.replace(/border-gray-100/g, 'border-gray-200 border-2');
            
            if (content !== newContent) {
                fs.writeFileSync(fullPath, newContent);
                console.log('Updated: ' + fullPath);
            }
        }
    }
}
processDir('/home/yawwnan/Project/HRIS SYSTEM/frontend/src/components/dashboard');

let sidebarPath = '/home/yawwnan/Project/HRIS SYSTEM/frontend/src/components/layout/AppSidebar.vue';
let sidebarContent = fs.readFileSync(sidebarPath, 'utf8');
sidebarContent = sidebarContent.replace(/border-r border-gray-200/g, 'border-r-2 border-gray-200');
fs.writeFileSync(sidebarPath, sidebarContent);
