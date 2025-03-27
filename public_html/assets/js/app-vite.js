// app.js

// Verifica si Alpine.js ya fue cargado desde el CDN
if (window.Alpine) {
	console.log('Alpine.js ya está disponible.');
	Alpine.start();
} else {
	console.warn('Alpine.js no está disponible. Asegúrate de cargarlo desde el CDN.');
}

// Configuración de Axios (si no lo configuras directamente en la plantilla Blade)
if (window.axios) {
	window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
	console.log('Axios configurado correctamente.');
} else {
	console.warn('Axios no está disponible. Asegúrate de cargarlo desde el CDN.');
}

// Código adicional que desees incluir
console.log('Archivo app.js cargado correctamente.');
