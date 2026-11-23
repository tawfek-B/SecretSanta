const Ziggy = {"url":"http:\/\/secret-santa.test:8000","port":8000,"defaults":{},"routes":{"boost.browser-logs":{"uri":"_boost\/browser-logs","methods":["POST"]},"sanctum.csrf-cookie":{"uri":"sanctum\/csrf-cookie","methods":["GET","HEAD"]},"home":{"uri":"\/","methods":["GET","HEAD"]},"users.store":{"uri":"users","methods":["POST"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]},"storage.local.upload":{"uri":"storage\/{path}","methods":["PUT"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
