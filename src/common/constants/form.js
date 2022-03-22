export const steps = {
  1: {
    name: 'Initial'
  },
  2: {
    name: 'Recurring',
    requirements: {
      recurring: true
    }
  },
  3: {
    name: 'Address',
    requirements: {
      address_enabled: true
    }
  },
  4: {
    name: 'Message',
    requirements: {
      message_enabled: true
    }
  },
  5: {
    name: 'Summary'
  }
}
