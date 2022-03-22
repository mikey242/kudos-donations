export const steps = {
  1: {
    name: 'initial'
  },
  2: {
    name: 'recurring',
    requirements: {
      recurring: true
    }
  },
  3: {
    name: 'address',
    requirements: {
      address_enabled: true
    }
  },
  4: {
    name: 'message',
    requirements: {
      message_enabled: true
    }
  },
  5: {
    name: 'summary'
  }
}
