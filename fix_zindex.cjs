const fs = require('fs');
const path = require('path');

const walkSync = (dir, filelist = []) => {
    fs.readdirSync(dir).forEach(file => {
        const dirFile = path.join(dir, file);
        try {
            if (fs.statSync(dirFile).isDirectory()) {
                filelist = walkSync(dirFile, filelist);
            } else {
                filelist.push(dirFile);
            }
        } catch (err) {}
    });
    return filelist;
};

const files = walkSync('resources/views').filter(f => f.endsWith('.blade.php'));
let modifiedCount = 0;

for (let file of files) {
    let content = fs.readFileSync(file, 'utf8');
    if (content.includes('z-index: 5;')) {
        content = content.replace(/style="margin-top: 0; position: relative; z-index: 5;"/g, 'style="margin-top: 0;"');
        fs.writeFileSync(file, content);
        console.log('Modified: ' + file);
        modifiedCount++;
    }
}
console.log('Total modified: ' + modifiedCount);
