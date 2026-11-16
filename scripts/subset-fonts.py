# /// script
# requires-python = ">=3.11"
# dependencies = ["fonttools==4.68.0", "brotli==1.2.1"]
# ///
"""Run `uv run scripts/subset-fonts.py` to regenerate the committed web fonts.

The originals in public/fonts remain the source. Normal application builds use
the committed subsets and do not require Python or FontTools.
"""

from pathlib import Path

from fontTools import subset
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parents[1]
# Latin and Latin Extended-A, combining accents, punctuation, currencies,
# and common symbols cover English/French
# without tying font coverage to today's CMS content.
UNICODES = "U+0000-017F,U+0300-036F,U+2000-206F,U+20A0-20CF,U+2122,U+2190-2199,U+2212,U+2215,U+FEFF,U+FFFD"

destination = ROOT / "resources/fonts"
destination.mkdir(exist_ok=True)

for name in ("InterVariable", "GeistVF"):
    source = ROOT / "public/fonts" / f"{name}.woff2"
    output = destination / f"{name}-latin.woff2"
    options = subset.Options()
    options.flavor = "woff2"
    options.name_IDs += [13, 14]  # Preserve the source license description and URL.
    font = TTFont(source, recalcTimestamp=False)
    subsetter = subset.Subsetter(options=options)
    subsetter.populate(unicodes=subset.parse_unicodes(UNICODES))
    subsetter.subset(font)
    font.save(output)
    print(f"{name}: {source.stat().st_size:,} → {output.stat().st_size:,} bytes")
