Note: the `*.gz` files in this directory are not the actual archives from GH Archives, 
but shrunk versions, with **2** lines per file (instead of ~170k lines).

The actual archives are too large to be included in this repository, and it would make PHPUnit tests crash due 
to memory exhaustion (I don't find a way to pass a stream or something equivalent to `MockResponse`).

To generate those files:
```bash
day='2024-10-07'
lines_per_file=2

for i in {0..23}; do
    curl -s "https://data.gharchive.org/${day}-${i}.json.gz" | zcat | head -n ${lines_per_file} | gzip > "${day}-${i}.json.gz"
done
```
