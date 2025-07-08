
-- Rule: Always re-analyze workspace and SQL files before changes
-- 1. Scan entire workspace for dependencies
-- 2. Parse and validate all SQL files
-- 3. Check for existing table definitions and relationships
-- 4. Verify data types and constraints
-- 5. Analyze query patterns and optimizations
-- 6. Only proceed with changes after full analysis is complete
-- 7. Don't Use any mock data


-- Implementation note: This rule should be applied automatically before any modifications
