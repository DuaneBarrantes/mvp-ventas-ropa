# comando SQL para la base de datos si desea crea un nuevo usario pero con la misma contraseña

INSERT INTO mvp_ropa.usuarios (usuario, clave, rol) -- 1. inserta una fila nueva en esta tabla, llenando estas 3 columnas
SELECT 'aprendiz1', clave, 'vendedor' -- 2. con estos valores: texto fijo, la clave de la fila encontrada, texto fijo
FROM mvp_ropa.usuarios -- 3. buscando en la misma tabla
WHERE usuario = 'admin'; -- 4. la fila cuyo usuario es admin
