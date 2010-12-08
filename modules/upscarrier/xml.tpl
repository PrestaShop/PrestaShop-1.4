<?xml version="1.0" ?>
	<AccessRequest xml:lang="en-US">
		<AccessLicenseNumber>[[AccessLicenseNumber]]</AccessLicenseNumber>
		<UserId>[[UserId]]</UserId>
		<Password>[[Password]]</Password>
	</AccessRequest>
	<?xml version="1.0" ?>
		<RatingServiceSelectionRequest>
			<Request>
				<TransactionReference>
					<CustomerContext>Rating and Service</CustomerContext>
					<XpciVersion>1.0</XpciVersion>
				</TransactionReference>
				<RequestAction>Rate</RequestAction>
				<RequestOption>Rate</RequestOption>
			</Request>
			<PickupType>
				<Code>[[PickupTypeCode]]</Code>
				<Description>Pickup Description</Description>
			</PickupType>
			<Shipment>
				<Description>Rate Shopping - Domestic</Description>
				<Shipper>
					<ShipperNumber>[[ShipperNumber]]</ShipperNumber>
					<Address>
						<AddressLine1>[[ShipperAddressLine1]]</AddressLine1>
						<AddressLine2>[[ShipperAddressLine2]]</AddressLine2>
						<AddressLine3 />
						<City>[[ShipperCity]]</City>
						<PostalCode>[[ShipperPostalCode]]</PostalCode>
						<CountryCode>[[ShipperCountryCode]]</CountryCode>
					</Address>
				</Shipper>
				<ShipTo>
					<Address>
						<AddressLine1>[[ShipToAddressLine1]]</AddressLine1>
						<AddressLine2>[[ShipToAddressLine2]]</AddressLine2>
						<AddressLine3 />
						<City>[[ShipToCity]]</City>
						<PostalCode>[[ShipToPostalCode]]</PostalCode>
						<CountryCode>[[ShipToCountryCode]]</CountryCode>
					</Address>
				</ShipTo>
				<ShipFrom>
					<Address>
						<AddressLine1>[[ShipFromAddressLine1]]</AddressLine1>
						<AddressLine2>[[ShipFromAddressLine2]]</AddressLine2>
						<AddressLine3 />
						<City>[[ShipFromCity]]</City>
						<PostalCode>[[ShipFromPostalCode]]</PostalCode>
						<CountryCode>[[ShipFromCountryCode]]</CountryCode>
					</Address>
				</ShipFrom>
				<Service><Code>65</Code></Service>
				<Package>
					<PackagingType>
						<Code>[[PackagingTypeCode]]</Code>
						<Description>Packaging Description</Description>
					</PackagingType>
					<Description>Rate</Description>
					<PackageWeight>
						<UnitOfMeasurement>
							<Code>KGS</Code>
						</UnitOfMeasurement>
						<Weight>[[PackageWeight]]</Weight>
					</PackageWeight>
				</Package>
				<ShipmentServiceOptions />
			</Shipment>
		</RatingServiceSelectionRequest>