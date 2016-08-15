DO
$body$
BEGIN
   IF NOT EXISTS (
      SELECT *
      FROM   pg_catalog.pg_user
      WHERE  usename = 'events'
   )
   THEN
      CREATE ROLE events SUPERUSER LOGIN;
   END IF;
END
$body$;
