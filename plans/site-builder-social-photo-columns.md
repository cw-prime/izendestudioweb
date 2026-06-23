# Site Builder Social + Photo Columns

> ✅ **APPLIED 2026-06-22** via Supabase MCP migration
> `add_social_links_and_uploaded_photos_to_site_builder_leads`. Both columns exist on
> `public.site_builder_leads` (text, nullable) and the PostgREST schema was reloaded. No need to re-run.

Run this against Supabase project `ocgearsjyqeoscjvcdrz` before deploying the social/photo intake fields:

```sql
alter table public.site_builder_leads
  add column if not exists social_links text,
  add column if not exists uploaded_photos text;

notify pgrst, 'reload schema';
```

The local service-role key cannot run DDL through PostgREST; use the Supabase SQL editor, MCP migration tool, or a direct database connection.
