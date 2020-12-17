echo "Exporting"
# Remove existing export folder
echo "Creating KUDOS_EXPORT folder"
rm -rf ../KUDOS_EXPORT && \
# Create new export folder
mkdir -p ../KUDOS_EXPORT/kudos-donations/
# Copy relevant files/folders to export
echo "Copying assets to KUDOS_EXPORT"
# shellcheck disable=SC2046
cp -R $(<export-list.txt) ../KUDOS_EXPORT/kudos-donations/ && \
# Remove unnecessary files
echo "Cleaning up"
cd ../KUDOS_EXPORT || exit
rm kudos-donations/languages/*.json
# Generating translations
echo "Generating translations"
wp i18n make-json kudos-donations/languages/ --no-purge
# Creating kudos-donations.zip
zip -qrD9 kudos-donations.zip kudos-donations