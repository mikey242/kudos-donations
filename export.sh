echo "Exporting"
# Remove existing export folder
echo "Creating build folder"
#rm -rf build && \
# Create new export folder
mkdir -p build/kudos-donations
# Scope dependencies
#echo "Scoping dependencies"
#php-scoper add-prefix --output-dir build/kudos-donations
echo "Building"
npm run production
# Copy relevant files/folders to export
echo "Copying built assets to build"
# shellcheck disable=SC2046
cp -R $(<export-list.txt) build/kudos-donations && \
cd build
# Composer dump-autoload
#cd kudos-donations && \
#composer dump-autoload
# Creating kudos-donations.zip
#cd ..
zip -qrD9 kudos-donations.zip kudos-donations