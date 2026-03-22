## Factories & Seeders

- Factory data must look like real production content — never use generic faker output (e.g. `fake()->words()`, `fake()->sentence()`) for user-visible fields. Instead, use curated arrays of realistic, domain-specific values with `fake()->randomElement()`.
- This is a trading card restoration/grading business. All seeded names, descriptions, and notes should relate to trading cards (Pokemon, Yu-Gi-Oh!, Magic: The Gathering, sports cards), card restoration processes (cleaning, pressing, grading prep), and the collector community.
- Check sibling factories in `database/factories/` for examples of how curated data arrays (e.g. `SHOP_NAMES`, `BIOS`) are used.
