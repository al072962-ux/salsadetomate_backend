const fs = require('fs');
const path = require('path');

const files = [
  'src/pages/Categories.jsx',
  'src/pages/Explore.jsx',
  'src/pages/RecipeDetail.jsx',
  'src/pages/RecipeEditor.jsx',
  'src/pages/RecipeMedia.jsx'
];

const newHeader = \
          <div className="flex gap-4">
            {localStorage.getItem('access_token') ? (
              <>
                <Link to="/explore" className="px-6 py-2.5 outline outline-2 outline-white text-white font-black text-lg md:text-xl rounded-full hover:bg-white/10 transition-colors hidden sm:block">Explorar</Link>
                <Link to="/my-recipes" className="px-6 py-2.5 bg-white text-[#ffb800] font-black text-lg md:text-xl rounded-full shadow hover:bg-gray-50 transition-colors">Mis recetas</Link>
                <button onClick={() => { localStorage.removeItem('access_token'); window.location.href='/login'; }} className="px-6 py-2.5 bg-white text-red-500 font-black text-lg md:text-xl rounded-full shadow hover:bg-gray-50 transition-colors border-2 border-red-100 hidden md:block">Salir</button>
              </>
            ) : (
              <>
                <Link to="/login" className="px-6 py-2.5 bg-white text-[#ffb800] font-black text-lg md:text-xl rounded-full shadow hover:bg-gray-50 transition-colors">Ingresar</Link>
                <Link to="/register" className="px-6 py-2.5 outline outline-2 outline-white text-white font-black text-lg md:text-xl rounded-full hover:bg-white/10 transition-colors">Regístrate</Link>
              </>
            )}
          </div>
\;

files.forEach(file => {
  const fullPath = path.join(__dirname, '../salsadetomate_frontend', file);
  if (fs.existsSync(fullPath)) {
    let content = fs.readFileSync(fullPath, 'utf8');
    // Using a more precise regex to ensure we don't destroy more divs than intended
    const regex = /<div className="flex gap-4">[\s\S]*?(?:<\/Link>|<\/button>)\s*<\/div>/;
    content = content.replace(regex, newHeader.trim());
    fs.writeFileSync(fullPath, content);
    console.log('Updated ' + file);
  }
});
